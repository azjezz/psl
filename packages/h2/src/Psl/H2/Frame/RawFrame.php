<?php

declare(strict_types=1);

namespace Psl\H2\Frame;

/**
 * Raw HTTP/2 frame as read from/written to the wire.
 *
 * Contains the frame header fields and the raw payload bytes
 * before any type-specific parsing.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-4.1
 */
final readonly class RawFrame
{
    /**
     * @param int<0, 255> $type Frame type identifier (0x0-0x9 for standard types).
     * @param int<0, 255> $flags Frame-type-specific boolean flags.
     * @param int<0, max> $streamId The stream identifier (0 for connection-level frames).
     * @param string $payload The raw frame payload bytes, before any type-specific parsing.
     */
    public function __construct(
        /**
         * Frame type identifier (0x0-0x9 for standard types).
         */
        public int $type,

        /**
         * Frame-type-specific boolean flags.
         */
        public int $flags,

        /**
         * The stream identifier (0 for connection-level frames).
         */
        public int $streamId,

        /**
         * The raw frame payload bytes.
         */
        public string $payload,
    ) {}
}
