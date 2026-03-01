<?php

declare(strict_types=1);

namespace Psl\Terminal;

/**
 * Represents a render frame, wraps the root Rect (full terminal) and the Buffer.
 *
 * Passed to the render callback on each frame tick.
 */
final class Frame
{
    private float $fps = 0.0;

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
     * Get the smoothed frames-per-second value for the current render cycle.
     *
     * The value is computed by the {@see Application} using an exponential moving average
     * and updated before each render callback invocation.
     */
    public function fps(): float
    {
        return $this->fps;
    }

    /**
     * Update the rect (used on terminal resize).
     */
    public function setRect(Rect $rect): void
    {
        $this->rect = $rect;
    }

    /**
     * Update the FPS value (used internally by Application).
     *
     * @internal
     */
    public function setFps(float $fps): void
    {
        $this->fps = $fps;
    }
}
