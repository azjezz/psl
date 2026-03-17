<?php

declare(strict_types=1);

namespace Psl\Terminal;

use Psl\DateTime;

/**
 * Represents a render frame, wraps the root Rect (full terminal) and the Buffer.
 *
 * Passed to the render callback on each frame tick.
 */
final class Frame
{
    private null|DateTime\Timestamp $lastDrawTimestamp = null;

    public function __construct(
        private Rect $rect,
        private readonly Buffer $buffer,
    ) {}

    /**
     * Get the root rect (full terminal area).
     */
    public function rect(): Rect
    {
        return $this->rect;
    }

    /**
     * Get the underlying buffer.
     */
    public function buffer(): Buffer
    {
        return $this->buffer;
    }

    /**
     * Get the monotonic timestamp of the last draw, or null if this is the first frame.
     */
    public function getLastDrawTimestamp(): null|DateTime\Timestamp
    {
        return $this->lastDrawTimestamp;
    }

    /**
     * Update the rect (used on terminal resize).
     */
    public function setRect(Rect $rect): void
    {
        $this->rect = $rect;
    }

    /**
     * Update the last draw timestamp (used internally by Application).
     *
     * @internal
     */
    public function setLastDrawTimestamp(DateTime\Timestamp $timestamp): void
    {
        $this->lastDrawTimestamp = $timestamp;
    }
}
