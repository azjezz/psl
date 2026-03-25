<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Internal\H2;

use Psl\Async;
use Psl\Async\SignalCancellationToken;
use Psl\H2\ClientConnectionInterface;
use Psl\H2\ErrorCode;
use Psl\H2\Event;
use Psl\HTTP\Client\Exception\ProtocolException;
use Psl\HTTP\Client\Exception\RuntimeException;

/**
 * H2 event dispatcher with a background read fiber.
 *
 * Reads H2 frames in a tight loop and dispatches events directly to per-stream
 * {@see H2Stream} objects. The read fiber runs `while (true)` and exits only
 * when cancelled (no more streams) or the connection dies.
 *
 * @internal
 */
final class H2Multiplexer
{
    /**
     * @var array<int, H2Stream>
     */
    private array $streams = [];

    private bool $fiberRunning = false;

    private SignalCancellationToken $fiberCancellation;

    private int $goAwayLastStreamId = -1;

    public function __construct(
        private readonly ClientConnectionInterface $connection,
        private readonly H2Session $session,
    ) {
        $this->fiberCancellation = new SignalCancellationToken();
    }

    /**
     * Register a stream for event dispatch. Starts the read fiber if not running.
     *
     * @throws RuntimeException If the stream ID exceeds the GOAWAY last-stream-id.
     */
    public function register(H2Stream $stream): void
    {
        if ($this->goAwayLastStreamId >= 0 && $stream->streamId > $this->goAwayLastStreamId) {
            throw new RuntimeException('HTTP/2 connection is closed.');
        }

        $this->streams[$stream->streamId] = $stream;

        if (!$this->fiberRunning) {
            $this->startReadFiber();
        }
    }

    /**
     * Unregister a stream. Stops the read fiber if no streams remain.
     */
    public function unregister(int $streamId): void
    {
        unset($this->streams[$streamId]);

        if ($this->streams === [] && $this->fiberRunning) {
            $this->fiberCancellation->cancel();
            $this->fiberCancellation = new SignalCancellationToken();
        }
    }

    private function startReadFiber(): void
    {
        $this->fiberRunning = true;
        $cancel = $this->fiberCancellation;

        Async\Scheduler::defer(function () use ($cancel): void {
            try {
                while (true) {
                    $events = $this->connection->readEvent($cancel);

                    foreach ($events as $event) {
                        $this->dispatch($event);
                    }
                }
            } catch (Async\Exception\CancelledException) {
                // @mago-expect lint:no-empty-catch-clause
            } catch (\Exception $e) {
                $this->session->markClosed();
                $wrapped =
                    $e instanceof RuntimeException || $e instanceof ProtocolException
                        ? $e
                        : new RuntimeException($e->getMessage(), previous: $e);
                foreach ($this->streams as $stream) {
                    $stream->fail($wrapped);
                }

                $this->streams = [];
            } finally {
                $this->fiberRunning = false;
            }
        });
    }

    private function dispatch(Event\EventInterface $event): void
    {
        if ($event instanceof Event\GoAwayReceived) {
            $this->handleGoAway($event);
            return;
        }

        $sid = match (true) {
            $event instanceof Event\DataReceived => $event->streamId,
            $event instanceof Event\HeadersReceived => $event->streamId,
            $event instanceof Event\StreamReset => $event->streamId,
            $event instanceof Event\StreamClosed => $event->streamId,
            $event instanceof Event\PushPromiseReceived => $event->streamId,
            default => null,
        };

        if ($sid === null || !isset($this->streams[$sid])) {
            return;
        }

        $stream = $this->streams[$sid];

        if ($event instanceof Event\HeadersReceived) {
            $stream->handleHeaders($event->headers, $event->endStream);
        } elseif ($event instanceof Event\DataReceived) {
            $stream->handleData($event->data, $event->endStream);
        } elseif ($event instanceof Event\StreamReset) {
            $stream->fail(ProtocolException::forMalformedResponse('Stream reset: ' . $event->errorCode->name));
            unset($this->streams[$sid]);
        }

        if ($event instanceof Event\StreamClosed) {
            unset($this->streams[$sid]);
        }

        if ($this->streams === [] && $this->fiberRunning) {
            $this->fiberCancellation->cancel();
            $this->fiberCancellation = new SignalCancellationToken();
        }
    }

    private function handleGoAway(Event\GoAwayReceived $event): void
    {
        $this->session->markClosed();

        if ($event->errorCode !== ErrorCode::NoError) {
            $error = new ProtocolException('Connection closed by server: ' . $event->errorCode->name);
            foreach ($this->streams as $stream) {
                $stream->fail($error);
            }

            $this->streams = [];
            $this->fiberCancellation->cancel();
            $this->fiberCancellation = new SignalCancellationToken();
            return;
        }

        $this->goAwayLastStreamId = $event->lastStreamId;
        $error = new RuntimeException('HTTP/2 connection is closed.');

        foreach ($this->streams as $sid => $stream) {
            if ($sid <= $event->lastStreamId) {
                continue;
            }

            $stream->fail($error);
            unset($this->streams[$sid]);
        }

        if ($this->streams === []) {
            $this->fiberCancellation->cancel();
            $this->fiberCancellation = new SignalCancellationToken();
        }
    }
}
