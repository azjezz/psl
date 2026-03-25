<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Internal\H2;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\HTTP\Client\Exception\ProtocolException;
use Psl\HTTP\Message\FieldMap;
use Psl\IO;
use Psl\IO\Exception;

use function strlen;

/**
 * Lazy, pull-based H2 response body handle.
 *
 * Reads from an {@see H2Stream}'s body buffer, which is fed by the
 * {@see H2Multiplexer}'s read fiber. The body handle has no direct
 * interaction with the read fiber - it only reads from the stream's
 * local buffer.
 *
 * @internal
 */
final class ResponseBodyHandle implements IO\ReadHandleInterface, IO\CloseHandleInterface
{
    use IO\ReadHandleConvenienceMethodsTrait;

    private bool $errored = false;

    private null|IO\Exception\RuntimeException $errorCause = null;

    /** @var non-negative-int */
    private int $bytesReceived = 0;

    private bool $closed = false;

    /**
     * @param positive-int $streamId
     */
    public function __construct(
        private readonly H2Stream $stream,
        private readonly H2Multiplexer $multiplexer,
        private readonly int $streamId,
        private readonly int $maxResponseBodySize,
    ) {}

    public function __destruct()
    {
        $this->close();
    }

    public function getTrailers(): null|FieldMap
    {
        return $this->stream->getTrailers();
    }

    public function tryRead(null|int $maxBytes = null): string
    {
        $this->throwIfErrored();

        return $this->stream->tryReadBody($maxBytes);
    }

    /**
     * @throws Exception\RuntimeException If the stream encountered an error.
     */
    public function read(
        null|int $maxBytes = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): string {
        $this->throwIfErrored();

        if ($this->stream->isBodyComplete()) {
            return '';
        }

        try {
            $data = $this->stream->readBody($maxBytes, $cancellation);
        } catch (\Throwable $e) {
            $this->setError($e);
            return '';
        }

        if ($data !== '') {
            $received = $this->bytesReceived + strlen($data);
            $this->bytesReceived = $received;

            if ($this->maxResponseBodySize > 0 && $this->bytesReceived > $this->maxResponseBodySize) {
                $this->setError(ProtocolException::forResponseBodyTooLarge($this->maxResponseBodySize));
                return '';
            }
        }

        if ($this->stream->isBodyComplete()) {
            $this->close();
        }

        return $data;
    }

    public function reachedEndOfDataSource(): bool
    {
        $this->throwIfErrored();

        return $this->stream->isBodyComplete();
    }

    public function isClosed(): bool
    {
        return $this->closed;
    }

    public function close(): void
    {
        if (!$this->closed) {
            $this->closed = true;
            $this->multiplexer->unregister($this->streamId);
        }
    }

    private function setError(\Throwable $cause): void
    {
        $this->errored = true;
        $this->errorCause = new Exception\RuntimeException(
            'H2 stream error: ' . $cause->getMessage(),
            previous: $cause,
        );
        $this->close();
    }

    /**
     * @throws Exception\RuntimeException
     */
    private function throwIfErrored(): void
    {
        if ($this->errored) {
            throw $this->errorCause ?? new Exception\RuntimeException('H2 stream error: unknown error');
        }
    }
}
