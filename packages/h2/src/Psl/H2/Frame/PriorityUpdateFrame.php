<?php

declare(strict_types=1);

namespace Psl\H2\Frame;

use Override;
use Psl\H2\Exception\FrameDecodingException;

use function pack;
use function strlen;
use function substr;
use function unpack;

/**
 * Signals stream priority using the extensible priority scheme (RFC 9218).
 *
 * PRIORITY_UPDATE frames are sent on stream 0 and carry the ID of the
 * stream being reprioritized along with a Priority Field Value encoded
 * as a Structured Fields serialization.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9218
 */
final readonly class PriorityUpdateFrame implements FrameInterface
{
    /**
     * Always 0 for PRIORITY_UPDATE frames (connection-level).
     *
     * @var int<0, max>
     */
    public int $streamId;

    /**
     * The frame type, always {@see FrameType::PriorityUpdate}.
     */
    public FrameType $type;

    /**
     * @param int<1, max> $prioritizedStreamId The stream whose priority is being updated.
     * @param string $fieldValue The Structured Fields serialized priority value (e.g. "u=0", "u=7, i").
     */
    public function __construct(
        public int $prioritizedStreamId,
        public string $fieldValue = '',
    ) {
        $this->streamId = 0;
        $this->type = FrameType::PriorityUpdate;
    }

    /**
     * @throws FrameDecodingException If the payload is too short or malformed.
     */
    #[Override]
    public static function fromRaw(RawFrame $frame): static
    {
        if (strlen($frame->payload) < 4) {
            throw FrameDecodingException::forInvalidPayload('PRIORITY_UPDATE', 'payload must be at least 4 bytes');
        }

        /** @var int<0, max> $prioritizedStreamId */
        $prioritizedStreamId = unpack('N', $frame->payload, 0)[1] & 0x7FFF_FFFF;

        if ($prioritizedStreamId === 0) {
            throw FrameDecodingException::forInvalidPayload('PRIORITY_UPDATE', 'prioritized stream ID must not be 0');
        }

        $fieldValue = strlen($frame->payload) > 4 ? substr($frame->payload, 4) : '';

        return new self($prioritizedStreamId, $fieldValue);
    }

    /**
     * Serialize to a RawFrame for wire encoding.
     *
     * Payload: 4-byte prioritized stream ID (31-bit) + field value bytes.
     */
    #[Override]
    public function toRaw(): RawFrame
    {
        return new RawFrame(
            FrameType::PriorityUpdate->value,
            0,
            0,
            pack('N', $this->prioritizedStreamId & 0x7FFF_FFFF) . $this->fieldValue,
        );
    }
}
