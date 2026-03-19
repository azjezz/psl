<?php

declare(strict_types=1);

namespace Psl\H2\Frame;

use Override;
use Psl\H2\Exception\FrameDecodingException;

/**
 * Continues a header block fragment from a preceding HEADERS or PUSH_PROMISE frame.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-6.10
 */
final readonly class ContinuationFrame implements FrameInterface
{
    /** The frame type, always {@see FrameType::Continuation}. */
    public FrameType $type;

    /**
     * @param int<1, max> $streamId The stream this continuation belongs to.
     * @param string $headerBlockFragment The HPACK-encoded header block fragment.
     * @param bool $endHeaders Whether this is the last continuation (END_HEADERS flag set).
     */
    public function __construct(
        public int $streamId,
        public string $headerBlockFragment,
        public bool $endHeaders,
    ) {
        $this->type = FrameType::Continuation;
    }

    /**
     * Parse a raw frame into a ContinuationFrame, extracting the header block fragment and END_HEADERS flag.
     *
     * @throws FrameDecodingException If the stream ID is zero.
     */
    #[Override]
    public static function fromRaw(RawFrame $frame): static
    {
        if (0 === $frame->streamId) {
            throw FrameDecodingException::forStreamIdRequired($frame->type);
        }

        $endHeaders = ($frame->flags & 0x04) !== 0;

        return new self($frame->streamId, $frame->payload, $endHeaders);
    }

    /**
     * Serialize this CONTINUATION frame into a RawFrame with the header block fragment and END_HEADERS flag.
     */
    #[Override]
    public function toRaw(): RawFrame
    {
        return new RawFrame(
            FrameType::Continuation->value,
            $this->endHeaders ? 0x04 : 0x00,
            $this->streamId,
            $this->headerBlockFragment,
        );
    }
}
