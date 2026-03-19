<?php

declare(strict_types=1);

namespace Psl\H2\Exception;

/**
 * Thrown when a received HTTP/2 frame cannot be decoded into a valid
 * structure.
 *
 * These errors indicate that the raw bytes read from the connection do
 * not conform to the frame layout rules defined in RFC 9113, Section 4.
 */
final class FrameDecodingException extends RuntimeException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    /**
     * Create a decoding exception when a frame type that must be
     * associated with a specific stream is received on stream 0.
     *
     * @param int $frameType The numeric frame type identifier.
     */
    public static function forStreamIdRequired(int $frameType): self
    {
        return new self('Stream ID required for frame type ' . $frameType);
    }

    /**
     * Create a decoding exception when the buffer does not contain
     * enough bytes to fully decode the frame payload.
     *
     * @param int $expected The number of bytes required for a complete frame.
     * @param int $actual The number of bytes available in the buffer.
     */
    public static function forInsufficientData(int $expected, int $actual): self
    {
        return new self('Insufficient frame data: need ' . $expected . ' bytes, have ' . $actual . '.');
    }

    /**
     * Create a decoding exception when a frame's payload does not
     * match the expected layout for its type.
     *
     * @param string $frameType The human-readable name of the frame type (e.g. "HEADERS", "SETTINGS").
     * @param string $reason A description of why the payload is invalid.
     */
    public static function forInvalidPayload(string $frameType, string $reason): self
    {
        return new self('Invalid ' . $frameType . ' frame payload: ' . $reason . '.');
    }

    /**
     * Create a decoding exception when the padding length field in a
     * padded frame exceeds the remaining payload length.
     *
     * @param int $paddingLength The declared padding length.
     * @param int $payloadLength The total payload length of the frame.
     */
    public static function forInvalidPaddingLength(int $paddingLength, int $payloadLength): self
    {
        return new self('Invalid padding length ' . $paddingLength . ' exceeds payload length ' . $payloadLength . '.');
    }

    /**
     * Create a decoding exception when a PING frame does not carry
     * exactly 8 bytes of opaque data as required by RFC 9113, Section 6.7.
     *
     * @param int $length The actual payload length received.
     */
    public static function forInvalidPingLength(int $length): self
    {
        return new self('PING frame payload must be 8 bytes, got ' . $length . '.');
    }

    /**
     * Create a decoding exception when a WINDOW_UPDATE frame contains
     * an increment of zero or a value exceeding 2^31 - 1.
     *
     * A zero increment is a protocol error per RFC 9113, Section 6.9.
     *
     * @param int $increment The invalid window size increment received.
     */
    public static function forInvalidWindowUpdateIncrement(int $increment): self
    {
        return new self('WINDOW_UPDATE increment must be between 1 and 2147483647, got ' . $increment . '.');
    }
}
