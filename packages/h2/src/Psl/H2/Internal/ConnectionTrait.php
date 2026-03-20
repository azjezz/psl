<?php

declare(strict_types=1);

namespace Psl\H2\Internal;

use Closure;
use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\H2\ConnectionInterface;
use Psl\H2\ErrorCode;
use Psl\H2\Exception\ConnectionException;
use Psl\H2\Exception\ProtocolException;
use Psl\H2\Exception\RuntimeException;
use Psl\H2\Frame;
use Psl\H2\Frame\RawFrame;
use Psl\H2\StreamState;
use Psl\IO;
use Revolt\EventLoop;
use Revolt\EventLoop\Suspension;
use Throwable;

use function array_filter;
use function array_values;
use function min;
use function ord;
use function strlen;
use function substr;
use function unpack;

use const Psl\H2\FRAME_HEADER_SIZE;

/**
 * Shared IO and protocol plumbing for HTTP/2 connections.
 *
 * Classes using this trait must declare:
 * - `private StateMachine $stateMachine`
 * - `private readonly IO\ReadHandleInterface&IO\WriteHandleInterface $handle`
 *
 * @internal
 *
 * @require-implements ConnectionInterface
 *
 * @mago-expect lint:kan-defect
 */
trait ConnectionTrait
{
    private readonly IO\ReadHandleInterface&IO\WriteHandleInterface $handle;

    private IO\Reader $reader;

    private string $readBuffer = '';

    private int $readBufferLength = 0;

    private string $writeBuffer = '';

    private int $writeBufferLength = 0;

    private int $writeBufferThreshold = 65_536;

    private bool $buffering = false;

    private StateMachine $stateMachine;

    /**
     * Registered window waiters, keyed by stream ID.
     *
     * Each entry is a list of [bytesNeeded, suspension] tuples waiting
     * for that stream's send window to open.
     *
     * @var array<int, list<array{int, Suspension}>>
     */
    private array $windowWaiters = [];

    /**
     * @inheritDoc
     */
    #[Override]
    public function isConnected(): bool
    {
        return !$this->stateMachine->shutdown;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function nextStreamId(): int
    {
        return $this->stateMachine->nextStreamId();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function lastPeerStreamId(): int
    {
        return $this->stateMachine->lastPeerStreamId();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getStreamState(int $streamId): StreamState
    {
        return $this->stateMachine->getStreamState($streamId);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function activeStreamCount(): int
    {
        return $this->stateMachine->activeStreamCount();
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function availableSendWindow(int $streamId): int
    {
        return $this->stateMachine->availableSendWindow($streamId);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function sendSettings(
        array $settings,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): void {
        $frames = $this->stateMachine->sendSettings($settings);
        $this->writeFrames($frames, $cancellation);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function readEvent(CancellationTokenInterface $cancellation = new NullCancellationToken()): array
    {
        $rawFrame = $this->readFrame($cancellation);

        try {
            [$responseFrames, $events] = $this->stateMachine->receive($rawFrame);
        } catch (RuntimeException $e) {
            // Auto-send GOAWAY before re-throwing.
            $errorCode = $e instanceof ProtocolException ? $e->errorCode : ErrorCode::ProtocolError;

            // @mago-expect lint:no-empty-catch-clause - Best effort, connection may already be broken.
            try {
                $goaway = $this->stateMachine->goAway($errorCode, $e->getMessage());
                $this->writeFrames($goaway);
            } catch (Throwable) {
            }

            throw $e;
        }

        if ($responseFrames !== []) {
            $data = '';
            foreach ($responseFrames as $frame) {
                $data .= Frame\encode($frame);
            }

            $this->write($data);
        }

        if ($this->windowWaiters !== []) {
            $this->notifyWindowWaiters($rawFrame->streamId);
        }

        return $events;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function sendAllData(
        int $streamId,
        string $data,
        bool $endStream = false,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): void {
        $offset = 0;
        $remaining = strlen($data);

        while ($remaining > 0) {
            $this->waitForSendWindow($streamId, 1, $cancellation);

            $window = $this->stateMachine->availableSendWindow($streamId);
            $chunkSize = min($remaining, $window);
            $isLast = $chunkSize === $remaining;

            $chunk = substr($data, $offset, $chunkSize);
            $this->sendData($streamId, $chunk, $endStream && $isLast, $cancellation);

            $offset += $chunkSize;
            $remaining -= $chunkSize;
        }

        if ($remaining === 0 && $endStream && $data === '') {
            // Empty data with endStream - send empty DATA frame.
            $this->sendData($streamId, '', true, $cancellation);
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function rejectPush(
        int $promisedStreamId,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): void {
        $this->resetStream($promisedStreamId, ErrorCode::Cancel, $cancellation);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function sendHeaders(
        int $streamId,
        array $headers,
        bool $endStream = false,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): void {
        $encoded = $this->stateMachine->sendHeadersEncoded($streamId, $headers, $endStream);
        $this->write($encoded, $cancellation);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function sendData(
        int $streamId,
        string $data,
        bool $endStream = false,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): void {
        $encoded = $this->stateMachine->sendDataEncoded($streamId, $data, $endStream);
        $this->write($encoded, $cancellation);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function resetStream(
        int $streamId,
        ErrorCode $errorCode,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): void {
        $frames = $this->stateMachine->resetStream($streamId, $errorCode);
        $this->writeFrames($frames, $cancellation);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function ping(
        string $opaqueData,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): void {
        $frames = $this->stateMachine->ping($opaqueData);
        $this->writeFrames($frames, $cancellation);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function goAway(
        ErrorCode $errorCode,
        string $debugData = '',
        null|int $lastStreamId = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): void {
        $frames = $this->stateMachine->goAway($errorCode, $debugData, $lastStreamId);
        $this->writeFrames($frames, $cancellation);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function waitForSendWindow(
        int $streamId,
        int $bytes,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): void {
        if ($this->stateMachine->availableSendWindow($streamId) >= $bytes) {
            return;
        }

        if ($this->stateMachine->shutdown) {
            throw ConnectionException::forConnectionClosed();
        }

        $cancellation->throwIfCancelled();
        $suspension = EventLoop::getSuspension();

        $this->windowWaiters[$streamId] ??= [];
        $this->windowWaiters[$streamId][] = [$bytes, $suspension];

        $id = null;
        if ($cancellation->cancellable) {
            $id = $cancellation->subscribe(static function (CancelledException $e) use ($suspension): void {
                $suspension->throw($e);
            });
        }

        try {
            $suspension->suspend();
        } finally {
            if ($id !== null) {
                $cancellation->unsubscribe($id);
            }

            // Remove this waiter from the stream's list.
            if (isset($this->windowWaiters[$streamId])) {
                $this->windowWaiters[$streamId] = array_values(array_filter(
                    $this->windowWaiters[$streamId],
                    static fn(array $w): bool => $w[1] !== $suspension,
                ));

                if ($this->windowWaiters[$streamId] === []) {
                    unset($this->windowWaiters[$streamId]);
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function buffered(Closure $fn, CancellationTokenInterface $cancellation = new NullCancellationToken()): void
    {
        if ($this->buffering) {
            throw ProtocolException::forConnectionError('Already buffering');
        }

        $this->buffering = true;
        try {
            $fn();
        } finally {
            $this->buffering = false;
            if ($this->writeBufferLength > 0) {
                $data = $this->writeBuffer;
                $this->writeBuffer = '';
                $this->writeBufferLength = 0;
                $this->handle->writeAll($data, $cancellation);
            }
        }
    }

    /**
     * Check window waiters for a specific stream and resume those whose condition is met.
     *
     * When $streamId is 0 (connection-level window update), all waiters across all
     * streams are checked since the connection window affects every stream.
     *
     * @param int<0, max> $streamId The stream that received a window update, or 0 for connection-level.
     */
    private function notifyWindowWaiters(int $streamId): void
    {
        if ($this->stateMachine->shutdown) {
            foreach ($this->windowWaiters as $waiters) {
                foreach ($waiters as [$bytes, $suspension]) {
                    $suspension->throw(ConnectionException::forConnectionClosed());
                }
            }

            return;
        }

        if ($streamId === 0) {
            foreach ($this->windowWaiters as $sid => $waiters) {
                foreach ($waiters as [$bytes, $suspension]) {
                    if ($this->stateMachine->availableSendWindow($sid) < $bytes) {
                        continue;
                    }

                    $suspension->resume();
                }
            }

            return;
        }

        if (!isset($this->windowWaiters[$streamId])) {
            return;
        }

        foreach ($this->windowWaiters[$streamId] as [$bytes, $suspension]) {
            if ($this->stateMachine->availableSendWindow($streamId) < $bytes) {
                continue;
            }

            $suspension->resume();
        }
    }

    /**
     * Read a complete frame from the underlying transport.
     *
     * @throws ConnectionException If the connection is closed.
     * @throws IO\Exception\AlreadyClosedException If the handle has been closed.
     * @throws IO\Exception\RuntimeException If an I/O error occurs.
     * @throws CancelledException If the cancellation token is cancelled.
     */
    private function readFrame(CancellationTokenInterface $cancellation = new NullCancellationToken()): RawFrame
    {
        while ($this->readBufferLength < FRAME_HEADER_SIZE) {
            $chunk = $this->reader->read(cancellation: $cancellation);
            if ($chunk === '' && $this->reader->reachedEndOfDataSource()) {
                throw ConnectionException::forConnectionClosed();
            }

            $this->readBuffer .= $chunk;
            $this->readBufferLength += strlen($chunk);
        }

        $length = (ord($this->readBuffer[0]) << 16) | (ord($this->readBuffer[1]) << 8) | ord($this->readBuffer[2]);
        $totalNeeded = FRAME_HEADER_SIZE + $length;

        while ($this->readBufferLength < $totalNeeded) {
            $chunk = $this->reader->read(cancellation: $cancellation);
            if ($chunk === '' && $this->reader->reachedEndOfDataSource()) {
                throw ConnectionException::forConnectionClosed();
            }

            $this->readBuffer .= $chunk;
            $this->readBufferLength += strlen($chunk);
        }

        $type = ord($this->readBuffer[3]);
        $flags = ord($this->readBuffer[4]);
        /** @var int<0, max> $streamId */
        $streamId = unpack('N', $this->readBuffer, 5)[1] & 0x7FFF_FFFF;
        $payload = $length > 0 ? substr($this->readBuffer, FRAME_HEADER_SIZE, $length) : '';
        $this->readBuffer = substr($this->readBuffer, $totalNeeded);
        $this->readBufferLength -= $totalNeeded;

        return new RawFrame($type, $flags, $streamId, $payload);
    }

    /**
     * Write data to the underlying handle, buffering if enabled.
     *
     * @throws IO\Exception\AlreadyClosedException If the handle has been closed.
     * @throws IO\Exception\RuntimeException If an I/O error occurs.
     * @throws CancelledException If the cancellation token is cancelled.
     */
    private function write(string $data, CancellationTokenInterface $cancellation = new NullCancellationToken()): void
    {
        if ($this->buffering) {
            $this->writeBuffer .= $data;
            $this->writeBufferLength += strlen($data);
            if ($this->writeBufferLength >= $this->writeBufferThreshold) {
                $data = $this->writeBuffer;
                $this->writeBuffer = '';
                $this->writeBufferLength = 0;
                $this->handle->writeAll($data, $cancellation);
            }

            return;
        }

        $this->handle->writeAll($data, $cancellation);
    }

    /**
     * Encode and write a list of raw frames.
     *
     * @param list<RawFrame> $frames
     *
     * @throws IO\Exception\AlreadyClosedException If the handle has been closed.
     * @throws IO\Exception\RuntimeException If an I/O error occurs.
     * @throws CancelledException If the cancellation token is cancelled.
     */
    private function writeFrames(
        array $frames,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): void {
        $data = '';
        foreach ($frames as $frame) {
            $data .= Frame\encode($frame);
        }

        $this->write($data, $cancellation);
    }
}
