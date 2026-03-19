<?php

declare(strict_types=1);

namespace Psl\H2\Internal;

use Psl\H2\Exception\ProtocolException;

use function strlen;

/**
 * Reassembles CONTINUATION frames into complete header blocks.
 *
 * @internal
 */
final class HeaderBlockAssembler
{
    /** @var null|int<0, max> Stream ID currently being assembled, or null if inactive. */
    private null|int $streamId = null;

    /** @var string Accumulated header block fragment bytes. */
    private string $buffer = '';

    /** @var bool Whether the END_STREAM flag was set on the initial HEADERS frame. */
    private bool $endStream = false;

    /** @var bool Whether the header block originates from a PUSH_PROMISE frame. */
    private bool $isPushPromise = false;

    /** @var int The promised stream ID when assembling a PUSH_PROMISE, or 0 otherwise. */
    private int $promisedStreamId = 0;

    /**
     * @param int $maxBufferSize Maximum accumulated header block size in bytes (0 = unlimited).
     */
    public function __construct(
        private readonly int $maxBufferSize = 0,
    ) {}

    /**
     * Whether a header block is currently being assembled (awaiting CONTINUATION frames).
     */
    public function isActive(): bool
    {
        return $this->streamId !== null;
    }

    /**
     * Return the stream ID being assembled, or null if no assembly is in progress.
     */
    public function activeStreamId(): null|int
    {
        return $this->streamId;
    }

    /**
     * @param int<0, max> $streamId
     *
     * @throws ProtocolException If the accumulated header block exceeds the maximum size.
     */
    public function startHeaders(int $streamId, string $fragment, bool $endStream): void
    {
        $this->streamId = $streamId;
        $this->buffer = $fragment;
        $this->endStream = $endStream;
        $this->isPushPromise = false;
        $this->promisedStreamId = 0;
        $this->checkBufferSize();
    }

    /**
     * @param int<0, max> $streamId
     *
     * @throws ProtocolException If the accumulated header block exceeds the maximum size.
     */
    public function startPushPromise(int $streamId, int $promisedStreamId, string $fragment): void
    {
        $this->streamId = $streamId;
        $this->buffer = $fragment;
        $this->endStream = false;
        $this->isPushPromise = true;
        $this->promisedStreamId = $promisedStreamId;
        $this->checkBufferSize();
    }

    /**
     * @throws ProtocolException If the accumulated header block exceeds the maximum size.
     */
    public function append(string $fragment): void
    {
        $this->buffer .= $fragment;
        $this->checkBufferSize();
    }

    /**
     * @return array{string, bool, bool, int}
     */
    public function complete(): array
    {
        $result = [$this->buffer, $this->endStream, $this->isPushPromise, $this->promisedStreamId];
        $this->reset();

        return $result;
    }

    /**
     * Reset the assembler to its initial state, discarding any accumulated data.
     */
    public function reset(): void
    {
        $this->streamId = null;
        $this->buffer = '';
        $this->endStream = false;
        $this->isPushPromise = false;
        $this->promisedStreamId = 0;
    }

    /**
     * @throws ProtocolException If accumulated buffer exceeds the configured maximum.
     */
    private function checkBufferSize(): void
    {
        if ($this->maxBufferSize > 0 && strlen($this->buffer) > $this->maxBufferSize) {
            $this->reset();

            throw ProtocolException::forConnectionError(
                'Header block size exceeds maximum of ' . $this->maxBufferSize . ' bytes',
            );
        }
    }
}
