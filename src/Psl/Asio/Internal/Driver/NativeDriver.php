<?php

declare(strict_types=1);

namespace Psl\Asio\Internal\Driver;

use Error;
use Exception;
use Psl\Asio;
use Psl\Asio\Internal\TimerQueue;
use Psl\Asio\Internal\Watcher;
use Throwable;

use function error_get_last;
use function random_int;
use function stream_select;
use function strpos;
use function usleep;

final class NativeDriver extends AbstractDriver
{
    /**
     * @var array<int, resource>
     */
    private array $readStreams = [];

    /**
     * @var array<int, array<string, Watcher<resource>>>
     */
    private array $readWatchers = [];

    /**
     * @var array<int, resource>
     */
    private array $writeStreams = [];

    /**
     * @var array<int, array<string, Watcher<resource>>>
     */
    private array $writeWatchers = [];

    private TimerQueue $timerQueue;

    /**
     * @var int Internal timestamp for now.
     */
    private int $now;

    /**
     * @var int Loop time offset
     */
    private int $nowOffset;

    public function __construct()
    {
        $this->timerQueue = new TimerQueue();
        $this->nowOffset = Asio\time();
        $this->now = random_int(0, $this->nowOffset);
        $this->nowOffset -= $this->now;
    }

    /**
     * {@inheritDoc}
     */
    public function now(): int
    {
        $this->now = Asio\time() - $this->nowOffset;

        return $this->now;
    }

    /**
     * {@inheritDoc}
     */
    public function getHandle()
    {
        return null;
    }

    /**
     * @throws Throwable
     */
    protected function dispatch(bool $blocking): void
    {
        $this->selectStreams(
            $this->readStreams,
            $this->writeStreams,
            $blocking ? $this->getTimeout() : 0
        );

        $now = $this->now();

        while ($watcher = $this->timerQueue->extract($now)) {
            if ($watcher->type & Watcher::REPEAT) {
                $watcher->enabled = false; // Trick base class into adding to enable queue when calling enable()
                $this->enable($watcher->id);
            } else {
                $this->cancel($watcher->id);
            }

            try {
                $watcher->execute();
            } catch (Throwable $exception) {
                $this->error($exception);
            }
        }
    }

    /**
     * @param array<int, resource> $read
     * @param array<int, resource> $write
     *
     * @throws Throwable
     */
    private function selectStreams(array $read, array $write, int $timeout): void
    {
        $timeout = (int) ($timeout / self::MILLISEC_PER_SEC);

        if (!empty($read) || !empty($write)) { // Use stream_select() if there are any streams in the loop.
            if ($timeout >= 0) {
                $seconds = (int) $timeout;
                $microseconds = (int) (($timeout - $seconds) * self::MICROSEC_PER_SEC);
            } else {
                $seconds = null;
                $microseconds = null;
            }

            $except = null;

            // Error reporting suppressed since stream_select() emits an E_WARNING if it is interrupted by a signal.
            if (!($result = @stream_select($read, $write, $except, $seconds, $microseconds))) {
                if ($result === 0) {
                    return;
                }

                $error = error_get_last();

                if (strpos($error["message"] ?? '', "unable to select") !== 0) {
                    return;
                }

                $this->error(new Exception($error["message"] ?? 'Unknown error during stream_select'));
            }

            foreach ($read as $stream) {
                $streamId = (int) $stream;
                if (!isset($this->readWatchers[$streamId])) {
                    continue; // All read watchers disabled.
                }

                foreach ($this->readWatchers[$streamId] as $watcher) {
                    if (!isset($this->readWatchers[$streamId][$watcher->id])) {
                        continue; // Watcher disabled by another IO watcher.
                    }

                    try {
                        $watcher->executeStream($stream);
                    } catch (Throwable $exception) {
                        $this->error($exception);
                    }
                }
            }

            /** @var list<resource> $write */
            foreach ($write as $stream) {
                $streamId = (int) $stream;
                if (!isset($this->writeWatchers[$streamId])) {
                    continue; // All write watchers disabled.
                }

                foreach ($this->writeWatchers[$streamId] as $watcher) {
                    if (!isset($this->writeWatchers[$streamId][$watcher->id])) {
                        continue; // Watcher disabled by another IO watcher.
                    }

                    try {
                        $watcher->executeStream($stream);
                    } catch (Throwable $exception) {
                        $this->error($exception);
                    }
                }
            }

            return;
        }

        if ($timeout > 0) { // Sleep until next timer expires.
            usleep((int) ($timeout * self::MICROSEC_PER_SEC));
        }
    }

    /**
     * @return int Milliseconds until next timer expires or -1 if there are no pending times.
     */
    private function getTimeout(): int
    {
        $expiration = $this->timerQueue->peek();

        if ($expiration === null) {
            return -1;
        }

        $expiration -= Asio\time() - $this->nowOffset;

        return $expiration > 0 ? $expiration : 0;
    }

    /**
     * {@inheritDoc}
     */
    protected function activate(array $watchers): void
    {
        foreach ($watchers as $watcher) {
            switch ($watcher->type) {
                case Watcher::READABLE:
                    /** @var Watcher<resource> $watcher */
                    $streamId = (int) $watcher->value;
                    $this->readWatchers[$streamId][$watcher->id] = $watcher;
                    $this->readStreams[$streamId] = $watcher->value;
                    break;

                case Watcher::WRITABLE:
                    /** @var Watcher<resource> $watcher */
                    $streamId = (int) $watcher->value;
                    $this->writeWatchers[$streamId][$watcher->id] = $watcher;
                    $this->writeStreams[$streamId] = $watcher->value;
                    break;

                case Watcher::DELAY:
                case Watcher::REPEAT:
                    /** @var Watcher<int> $watcher */
                    $this->timerQueue->insert($watcher);
                    break;

                default:
                    // @codeCoverageIgnoreStart
                    throw new Error("Unknown watcher type");
                    // @codeCoverageIgnoreEnd
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function deactivate(Watcher $watcher): void
    {
        switch ($watcher->type) {
            case Watcher::READABLE:
                /** @var Watcher<resource> $watcher */
                $streamId = (int) $watcher->value;
                unset($this->readWatchers[$streamId][$watcher->id]);
                if (empty($this->readWatchers[$streamId])) {
                    unset($this->readWatchers[$streamId], $this->readStreams[$streamId]);
                }
                break;

            case Watcher::WRITABLE:
                /** @var Watcher<resource> $watcher */
                $streamId = (int) $watcher->value;
                unset($this->writeWatchers[$streamId][$watcher->id]);
                if (empty($this->writeWatchers[$streamId])) {
                    unset($this->writeWatchers[$streamId], $this->writeStreams[$streamId]);
                }
                break;

            case Watcher::DELAY:
            case Watcher::REPEAT:
                /** @var Watcher<int> $watcher */
                $this->timerQueue->remove($watcher);
                break;

            default:
                // @codeCoverageIgnoreStart
                throw new Error("Unknown watcher type");
                // @codeCoverageIgnoreEnd
        }
    }
}
