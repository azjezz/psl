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
 * Advertises alternative services per RFC 7838.
 *
 * On stream 0, the frame carries an explicit origin and applies to that origin.
 * On a non-zero stream, the origin field is empty and the frame applies to the
 * origin of the request on that stream.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc7838#section-4
 */
final readonly class AltSvcFrame implements FrameInterface
{
    /**
     * The frame type, always {@see FrameType::AltSvc}.
     */
    public FrameType $type;

    /**
     * @param int<0, max> $streamId The stream ID (0 for explicit origin, non-zero for stream's origin).
     * @param string $origin The origin this alternative service applies to (non-empty when streamId is 0).
     * @param string $fieldValue The Alt-Svc field value (e.g. 'h3=":443"; ma=2592000').
     */
    public function __construct(
        public int $streamId,
        public string $origin,
        public string $fieldValue,
    ) {
        $this->type = FrameType::AltSvc;
    }

    /**
     * @throws FrameDecodingException If the payload is malformed.
     */
    #[Override]
    public static function fromRaw(RawFrame $frame): static
    {
        $payloadLength = strlen($frame->payload);
        if ($payloadLength < 2) {
            throw FrameDecodingException::forInvalidPayload('ALTSVC', 'payload must be at least 2 bytes');
        }

        /** @var int<0, max> $originLength */
        $originLength = unpack('n', $frame->payload, 0)[1];

        if ($payloadLength < (2 + $originLength)) {
            throw FrameDecodingException::forInvalidPayload('ALTSVC', 'payload too short for origin length');
        }

        $origin = $originLength > 0 ? substr($frame->payload, 2, $originLength) : '';
        $fieldValue = substr($frame->payload, 2 + $originLength);

        return new self($frame->streamId, $origin, $fieldValue);
    }

    /**
     * Serialize to a RawFrame for wire encoding.
     *
     * Payload: 2-byte origin length + origin + field value.
     */
    #[Override]
    public function toRaw(): RawFrame
    {
        return new RawFrame(
            FrameType::AltSvc->value,
            0,
            $this->streamId,
            pack('n', strlen($this->origin)) . $this->origin . $this->fieldValue,
        );
    }
}
