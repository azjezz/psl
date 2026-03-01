<?php

declare(strict_types=1);

namespace Psl\Terminal;

use Closure;
use Psl\Ansi;
use Psl\Async;
use Psl\DateTime;
use Psl\IO;
use Psl\Math;
use Psl\Terminal\Internal\EventParser;
use Psl\Terminal\Internal\RawMode;
use Psl\Terminal\Internal\TerminalSize;

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
    private null|DateTime\Timestamp $lastFrameTime = null;
    private float $currentFps = 0.0;

    /**
     * @param S $state
     */
    private function __construct(
        private string $title,
        private int $fps,
        private object $state,
        private IO\ReadHandleInterface&IO\StreamHandleInterface $input,
        private IO\WriteHandleInterface $output,
        private null|Internal\ScrollSmoothing $scrollSmoothing,
        private bool $mouseMotion,
    ) {}

    /**
     * Create a new terminal application.
     *
     * When no input/output handles are provided, the application uses the local terminal
     * (STDIN/STDOUT) with raw mode, signal handling, and terminal size detection.
     *
     * For remote scenarios (e.g. an SSH server), provide custom input and output handles.
     * In this mode, raw mode and signal handling are skipped (the remote client manages those).
     * Dispatch a {@see Event\Resize} event to set the initial terminal size.
     *
     * @template T of object
     *
     * @param T $state Application state object, passed to all callbacks.
     * @param positive-int $fps Target frames per second for the render loop (default: 60).
     * @param null|(IO\ReadHandleInterface&IO\StreamHandleInterface) $input
     *
     * @return self<T>
     */
    public static function create(
        object $state,
        string $title = '',
        int $fps = 60,
        (IO\ReadHandleInterface&IO\StreamHandleInterface)|null $input = null,
        null|IO\WriteHandleInterface $output = null,
        bool $scrollSmoothing = true,
        bool $mouseMotion = false,
    ): self {
        return new self(
            $title,
            $fps,
            $state,
            $input ?? IO\input_handle(),
            $output ?? IO\output_handle(),
            $scrollSmoothing ? new Internal\ScrollSmoothing() : null,
            $mouseMotion,
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

        $isTty = IO\is_terminal($this->input);

        [$cols, $rows] = $isTty ? TerminalSize::get() : [80, 24];

        $buffer = new Buffer($cols, $rows);
        $rect = Rect::fromSize($cols, $rows);
        $frame = new Frame($rect, $buffer);

        $rawMode = $isTty ? new RawMode() : null;
        $rawMode?->enable();

        try {
            $setupSequences = Ansi\Screen\enable_alternate_screen()->toString();
            $setupSequences .= Ansi\Cursor\hide()->toString();
            $setupSequences .= Ansi\Screen\enable_mouse_tracking($this->mouseMotion)->toString();
            $setupSequences .= Ansi\Screen\enable_bracketed_paste()->toString();
            $setupSequences .= Ansi\Screen\enable_focus_tracking()->toString();
            $setupSequences .= Ansi\Screen\enable_kitty_keyboard()->toString();

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

            // Register input readable handler
            $inputId = Async\Scheduler::onReadable($stream, function () use ($eventParser): void {
                try {
                    $data = $this->input->tryRead();
                } catch (IO\Exception\AlreadyClosedException) {
                    // Input handle closed (e.g. SSH client disconnected)
                    $this->stop(1);
                    return;
                }

                if ($data === '') {
                    return;
                }

                $events = $eventParser->feed($data);
                foreach ($events as $event) {
                    $this->dispatchEvent($event);
                }
            });

            $sigwinchId = null;
            if ($isTty && defined('SIGWINCH')) {
                $sigwinchId = Async\Scheduler::onSignal(SIGWINCH, function () use ($frame, $buffer): void {
                    [$cols, $rows] = TerminalSize::get();
                    $buffer->resize($cols, $rows);
                    $frame->setRect(Rect::fromSize($cols, $rows));
                    $this->dispatchEvent(new Event\Resize($cols, $rows));
                });
            }

            $sigintId = null;
            if (defined('SIGINT')) {
                $sigintId = Async\Scheduler::onSignal(SIGINT, function (): void {
                    $this->dispatchEvent(Event\Key::named('ctrl+c'));
                });
            }

            $frameInterval = DateTime\Duration::microseconds(Math\maxva(Math\div(1_000_000, $this->fps), 1));
            $renderTimerId = Async\Scheduler::repeat($frameInterval, function () use (
                $callback,
                $frame,
                $buffer,
            ): void {
                if (!$this->running) {
                    return;
                }

                $this->render($callback, $frame, $buffer);
            });

            $this->render($callback, $frame, $buffer);

            while ($this->running) {
                Async\later();
            }

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
            $this->tryTeardown();
            $rawMode?->restore();
        }

        return $this->exitCode;
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

        $now = DateTime\Timestamp::monotonic();
        if ($this->lastFrameTime !== null) {
            $delta = $now->since($this->lastFrameTime)->getTotalSeconds();
            if ($delta > 0.0) {
                $instantFps = 1.0 / $delta;
                $this->currentFps = $this->currentFps > 0.0
                    ? ($this->currentFps * 0.9) + ($instantFps * 0.1)
                    : $instantFps;
            }
        }

        $this->lastFrameTime = $now;
        $frame->setFps($this->currentFps);

        try {
            $callback($frame, $this->state);
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
            $teardownSequences = Ansi\Screen\disable_kitty_keyboard()->toString();
            $teardownSequences .= Ansi\Screen\disable_focus_tracking()->toString();
            $teardownSequences .= Ansi\Screen\disable_bracketed_paste()->toString();
            $teardownSequences .= Ansi\Screen\disable_mouse_tracking($this->mouseMotion)->toString();
            $teardownSequences .= Ansi\Screen\erase(Ansi\Screen\EraseMode::Full)->toString();
            $teardownSequences .= Ansi\reset()->toString();
            $teardownSequences .= Ansi\Cursor\move_to(1, 1)->toString();
            $teardownSequences .= Ansi\Cursor\show()->toString();
            $teardownSequences .= Ansi\Screen\disable_alternate_screen()->toString();

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

    private function dispatchEvent(Event\Key|Event\Mouse|Event\Paste|Event\Resize|Event\Focus $event): void
    {
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
}
