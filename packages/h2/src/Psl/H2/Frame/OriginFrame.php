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
 * Declares authoritative origins for connection coalescing per RFC 8336.
 *
 * ORIGIN frames MUST be sent on stream 0 and carry a list of ASCII-serialized
 * origins that the server considers itself authoritative for. Clients use this
 * to determine whether they can reuse the connection for requests to those origins.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc8336
 */
final readonly class OriginFrame implements FrameInterface
{
    /**
     * Always 0 for ORIGIN frames (connection-level).
     *
     * @var int<0, max>
     */
    public int $streamId;

    /**
     * The frame type, always {@see FrameType::Origin}.
     */
    public FrameType $type;

    /**
     * @param list<non-empty-string> $origins The origins the server is authoritative for
     *                                        (e.g. "https://example.com", "https://cdn.example.com:8443").
     */
    public function __construct(
        public array $origins,
    ) {
        $this->streamId = 0;
        $this->type = FrameType::Origin;
    }

    /**
     * @throws FrameDecodingException If the payload is malformed.
     */
    #[Override]
    public static function fromRaw(RawFrame $frame): static
    {
        $payload = $frame->payload;
        $payloadLength = strlen($payload);
        $offset = 0;
        $origins = [];

        while ($offset < $payloadLength) {
            if (($payloadLength - $offset) < 2) {
                throw FrameDecodingException::forInvalidPayload('ORIGIN', 'truncated origin length');
            }

            /** @var int<0, max> $originLength */
            $originLength = unpack('n', $payload, $offset)[1];
            $offset += 2;

            if (($payloadLength - $offset) < $originLength) {
                throw FrameDecodingException::forInvalidPayload('ORIGIN', 'truncated origin value');
            }

            if ($originLength > 0) {
                /** @var non-empty-string $origin */
                $origin = substr($payload, $offset, $originLength);
                $origins[] = $origin;
            }

            $offset += $originLength;
        }

        return new self($origins);
    }

    /**
     * Serialize to a RawFrame for wire encoding.
     *
     * Payload: repeated [2-byte length + origin bytes].
     */
    #[Override]
    public function toRaw(): RawFrame
    {
        $payload = '';
        foreach ($this->origins as $origin) {
            $payload .= pack('n', strlen($origin)) . $origin;
        }

        return new RawFrame(FrameType::Origin->value, 0, 0, $payload);
    }
}
