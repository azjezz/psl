<?php

declare(strict_types=1);

namespace Psl\H2\Frame;

use Psl\H2\Exception\FrameDecodingException;

/**
 * @psalm-inheritors DataFrame|HeadersFrame|PriorityFrame|RstStreamFrame|SettingsFrame|PushPromiseFrame|PingFrame|GoAwayFrame|WindowUpdateFrame|ContinuationFrame|PriorityUpdateFrame|AltSvcFrame|OriginFrame
 */
interface FrameInterface
{
    /**
     * The stream identifier this frame belongs to.
     *
     * Zero for connection-level frames (SETTINGS, PING, GOAWAY, PRIORITY_UPDATE, ...etc).
     *
     * @var int<0, max>
     */
    public int $streamId { get; }

    /**
     * The frame type identifier.
     */
    public FrameType $type { get; }

    /**
     * Parse a RawFrame into this typed frame, extracting type-specific fields from the payload.
     *
     * @throws FrameDecodingException If the raw frame cannot be decoded into this frame type.
     */
    public static function fromRaw(RawFrame $frame): static;

    /**
     * Serialize this typed frame back into a RawFrame for wire encoding.
     */
    public function toRaw(): RawFrame;
}
