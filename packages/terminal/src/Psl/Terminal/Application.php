<?php

declare(strict_types=1);

namespace Psl\Terminal;

use Closure;
use Psl\Ansi;
use Psl\Async;
use Psl\DateTime;
use Psl\DateTime\Duration;
use Psl\IO;
use Psl\IO\ReadHandleInterface;
use Psl\IO\StreamHandleInterface;
use Psl\IO\WriteHandleInterface;
use Psl\Terminal\Internal\EventParser;

use function defined;
use function is_resource;
use function substr;

/**
 * Terminal application managing the event loop, raw mode, and rendering lifecycle.
 *
 * @template S of object
 */
final class Application
{
    /**
     * @var array<class-string, list<Closure>>
     */
    private array $eventHandlers = [];

    /**
     * @var list<array{DateTime\Duration, Closure(S): void}>
     */
    private array $intervals = [];

    /**
     * @var list<Ansi\CommandInterface>
     */
    private array $pendingCommands = [];

    private int $exitCode = 0;
    private bool $running = false;
    private bool $rendering = false;
    private null|Buffer $buffer = null;
    private null|Frame $frame = null;

    private null|Async\Deferred $stopDeferred = null;

    /**
     * @param S $state
     *
     * @mago-expect lint:excessive-parameter-list
     */
    private function __construct(
        private readonly string $title,
        private readonly DateTime\Duration $tickInterval,
        private readonly object $state,
        private readonly IO\ReadHandleInterface&IO\StreamHandleInterface $input,
        private readonly IO\WriteHandleInterface $output,
        private readonly null|Internal\ScrollSmoothing $scrollSmoothing,
        private readonly Ansi\Screen\ScreenMode $mouseMode,
        private readonly RawModeSwitcherInterface $rawModeSwitcher,
        private readonly WindowSizeProviderInterface $windowSizeProvider,
        private readonly bool $remote,
    ) {}

    /**
     * Create a local terminal application using STDIN/STDOUT.
     *
     * @template T of object
     *
     * @param T $state Application state object, passed to all callbacks.
     * @param null|DateTime\Duration $tickInterval How often the render tick fires (e.g. Duration::milliseconds(16) for ~60 ticks/s).
     *
     * @return self<T>
     */
    public static function create(
        object $state,
        string $title = '',
        null|DateTime\Duration $tickInterval = null,
        bool $scrollSmoothing = true,
        bool $mouseMotion = false,
    ): self {
        return new self(
            $title,
            $tickInterval ?? DateTime\Duration::milliseconds(16),
            $state,
            IO\input_handle(),
            IO\output_handle(),
            $scrollSmoothing ? new Internal\ScrollSmoothing() : null,
            $mouseMotion ? Ansi\Screen\ScreenMode::MouseMotionTracking : Ansi\Screen\ScreenMode::MouseTracking,
            new LocalRawModeSwitcher(),
            new LocalWindowSizeProvider(),
            false,
        );
    }

    /**
     * Create a terminal application with custom I/O handles.
     *
     * Use this for remote scenarios (e.g. SSH servers) where you provide
     * your own input/output streams, or for testing where you want full
     * control over the terminal environment.
     *
     * Raw mode is not managed; the caller is responsible for it.
     * Signal handlers (SIGWINCH, SIGINT) are not registered; ctrl+c
     * is handled via the input stream parser instead.
     *
     * Use {@see dispatch()} to inject {@see Event\Resize} events whenever
     * the remote client reports a window size change.
     *
     * @template T of object
     *
     * @param T $state Application state object, passed to all callbacks.
     * @param ReadHandleInterface&StreamHandleInterface $input
     * @param WriteHandleInterface $output
     * @param int $width Initial terminal width (columns).
     * @param int $height Initial terminal height (rows).
     * @param Duration|null $tickInterval How often the render tick fires (e.g. Duration::milliseconds(16) for ~60 ticks/s).
     *
     * @return self<T>
     */
    public static function custom(
        object $state,
        IO\ReadHandleInterface&IO\StreamHandleInterface $input,
        IO\WriteHandleInterface $output,
        int $width,
        int $height,
        string $title = '',
        null|DateTime\Duration $tickInterval = null,
        bool $scrollSmoothing = true,
        bool $mouseMotion = false,
    ): self {
        return new self(
            $title,
            $tickInterval ?? DateTime\Duration::milliseconds(16),
            $state,
            $input,
            $output,
            $scrollSmoothing ? new Internal\ScrollSmoothing() : null,
            $mouseMotion ? Ansi\Screen\ScreenMode::MouseMotionTracking : Ansi\Screen\ScreenMode::MouseTracking,
            new NoopRawModeSwitcher(),
            new StaticWindowSizeProvider($width, $height),
            true,
        );
    }

    /**
     * Register an event handler for a specific event type.
     *
     * @template T of Event\Key|Event\Mouse|Event\Paste|Event\Resize|Event\Focus
     *
     * @param class-string<T> $eventClass
     * @param Closure(T, S): void $handler
     */
    public function on(string $eventClass, Closure $handler): void
    {
        $this->eventHandlers[$eventClass] ??= [];
        $this->eventHandlers[$eventClass][] = $handler;
    }

    /**
     * Register a periodic callback.
     *
     * @param Closure(S): void $callback
     */
    public function interval(DateTime\Duration $interval, Closure $callback): void
    {
        $this->intervals[] = [$interval, $callback];
    }

    /**
     * Stop the application event loop.
     */
    public function stop(int $exitCode = 0): void
    {
        $this->exitCode = $exitCode;
        $this->running = false;
        $this->stopDeferred?->complete(null);
    }

    /**
     * Emit a command sequence to the terminal output.
     *
     * The command is queued and written after the next frame render.
     * This is the safe way to send escape sequences (e.g. OSC progress indicators)
     * without interfering with buffer rendering.
     */
    public function emit(Ansi\CommandInterface $command): void
    {
        $this->pendingCommands[] = $command;
    }

    /**
     * Enter the event loop: enable raw mode, alternate screen, mouse tracking, etc.
     *
     * Blocks until {@see stop()} is called. Returns the exit code.
     *
     * @param Closure(Frame, S): void $callback Render callback, called on each frame tick.
     *
     * @throws Exception\RuntimeException If unable to set up the terminal.
     */
    public function run(Closure $callback): int
    {
        $eventParser = new EventParser();
        $stream = $this->input->getStream();

        if (!is_resource($stream)) {
            throw new Exception\RuntimeException('Input handle must provide an underlying stream resource.');
        }

        [$cols, $rows] = $this->windowSizeProvider->get();

        $buffer = new Buffer($cols, $rows);
        $frame = new Frame(Rect::fromSize($cols, $rows), $buffer);

        $this->buffer = $buffer;
        $this->frame = $frame;

        $this->rawModeSwitcher->enable();

        try {
            $setupSequences = Ansi\Screen\set_mode(Ansi\Screen\ScreenMode::AlternateScreen)->toString();
            $setupSequences .= Ansi\Cursor\hide()->toString();
            $setupSequences .= Ansi\Screen\set_mode($this->mouseMode)->toString();
            $setupSequences .= Ansi\Screen\set_mode(Ansi\Screen\ScreenMode::BracketedPaste)->toString();
            $setupSequences .= Ansi\Screen\set_mode(Ansi\Screen\ScreenMode::FocusTracking)->toString();
            $setupSequences .= Ansi\Screen\enable_kitty_keyboard()->toString();
            $setupSequences .= Ansi\Screen\set_mode(Ansi\Screen\ScreenMode::InBandResize)->toString();

            if ($this->title !== '') {
                $setupSequences .= Ansi\Screen\title($this->title)->toString();
            }

            $this->output->writeAll($setupSequences);

            $this->running = true;

            $state = $this->state;
            $timerIds = [];
            foreach ($this->intervals as [$intervalDuration, $intervalCallback]) {
                $timerIds[] = Async\Scheduler::repeat($intervalDuration, static function () use (
                    $intervalCallback,
                    $state,
                ): void {
                    $intervalCallback($state);
                });
            }

            $inputId = Async\Scheduler::onReadable($stream, function () use ($eventParser): void {
                try {
                    $data = $this->input->tryRead();
                } catch (IO\Exception\AlreadyClosedException) {
                    // input handle closed (e.g. SSH client disconnected)
                    $this->stop(1);
                    return;
                }

                if ($data === '') {
                    return;
                }

                $events = $eventParser->feed($data);
                foreach ($events as $event) {
                    $this->dispatch($event);
                }
            });

            $sigwinchId = null;
            if (!$this->remote && defined('SIGWINCH')) {
                $sigwinchId = Async\Scheduler::onSignal(SIGWINCH, function (): void {
                    [$cols, $rows] = $this->windowSizeProvider->get();
                    $this->dispatch(new Event\Resize($cols, $rows));
                });
            }

            $sigintId = null;
            if (!$this->remote && defined('SIGINT')) {
                $sigintId = Async\Scheduler::onSignal(SIGINT, function (): void {
                    $this->dispatch(Event\Key::named('ctrl+c'));
                });
            }

            $renderTimerId = Async\Scheduler::repeat($this->tickInterval, function () use (
                $callback,
                $frame,
                $buffer,
                $eventParser,
            ): void {
                if (!$this->running) {
                    return;
                }

                $pending = $eventParser->flushPending();
                foreach ($pending as $event) {
                    $this->dispatch($event);
                }

                $this->render($callback, $frame, $buffer);
            });

            $this->render($callback, $frame, $buffer);

            $deferred = new Async\Deferred();
            $this->stopDeferred = $deferred;
            $deferred->getAwaitable()->await();

            Async\Scheduler::cancel($renderTimerId);
            Async\Scheduler::cancel($inputId);
            foreach ($timerIds as $timerId) {
                Async\Scheduler::cancel($timerId);
            }

            if ($sigwinchId !== null) {
                Async\Scheduler::cancel($sigwinchId);
            }

            if ($sigintId !== null) {
                Async\Scheduler::cancel($sigintId);
            }
        } finally {
            $this->buffer = null;
            $this->frame = null;
            $this->tryTeardown();
            $this->rawModeSwitcher->restore();
        }

        return $this->exitCode;
    }

    /**
     * Dispatch an event to the application.
     *
     * This is useful for injecting events from external sources (e.g. SSH window-change messages).
     *
     * Resize events automatically update the internal buffer and frame dimensions.
     */
    public function dispatch(Event\Key|Event\Mouse|Event\Paste|Event\Resize|Event\Focus $event): void
    {
        if ($event instanceof Event\Resize) {
            $this->buffer?->resize($event->width, $event->height);
            $this->frame?->setRect(Rect::fromSize($event->width, $event->height));
        }

        if ($this->scrollSmoothing !== null && $event instanceof Event\Mouse) {
            if (!$this->scrollSmoothing->filter($event)) {
                return;
            }
        }

        $class = $event::class;
        $handlers = $this->eventHandlers[$class] ?? [];

        foreach ($handlers as $handler) {
            $handler($event, $this->state);

            if (!$this->running) {
                break;
            }
        }
    }

    /**
     * @param Closure(Frame, S): void $callback
     */
    private function render(Closure $callback, Frame $frame, Buffer $buffer): void
    {
        if ($this->rendering) {
            return;
        }

        $this->rendering = true;

        $buffer->clear();

        try {
            $callback($frame, $this->state);
            $frame->setLastDrawTimestamp(DateTime\Timestamp::monotonic());
            $buffer->flush($this->output);

            if ($this->pendingCommands !== []) {
                $sequences = '';
                foreach ($this->pendingCommands as $command) {
                    $sequences .= $command->toString();
                }

                $this->pendingCommands = [];
                $this->output->writeAll($sequences);
            }
        } catch (IO\Exception\AlreadyClosedException) {
            $this->stop(1);
        } finally {
            $this->rendering = false;
        }
    }

    /**
     * Attempt to write teardown sequences to the output, returning false if the output is already closed.
     */
    private function tryTeardown(): void
    {
        try {
            $teardownSequences = Ansi\Screen\reset_mode(Ansi\Screen\ScreenMode::InBandResize)->toString();
            $teardownSequences .= Ansi\Screen\disable_kitty_keyboard()->toString();
            $teardownSequences .= Ansi\Screen\reset_mode(Ansi\Screen\ScreenMode::FocusTracking)->toString();
            $teardownSequences .= Ansi\Screen\reset_mode(Ansi\Screen\ScreenMode::BracketedPaste)->toString();
            $teardownSequences .= Ansi\Screen\reset_mode($this->mouseMode)->toString();
            $teardownSequences .= Ansi\Screen\erase(Ansi\Screen\EraseMode::Full)->toString();
            $teardownSequences .= Ansi\reset()->toString();
            $teardownSequences .= Ansi\Cursor\move_to(1, 1)->toString();
            $teardownSequences .= Ansi\Cursor\show()->toString();
            $teardownSequences .= Ansi\Screen\reset_mode(Ansi\Screen\ScreenMode::AlternateScreen)->toString();

            $written = 1;
            while ($teardownSequences !== '' && $written > 0) {
                $written = $this->output->tryWrite($teardownSequences);
                $teardownSequences = substr($teardownSequences, $written);
            }

            return;
        } catch (IO\Exception\AlreadyClosedException) {
            return;
        }
    }
}
