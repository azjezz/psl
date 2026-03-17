<?php

declare(strict_types=1);

namespace Psl\Terminal;

use Psl\Math;

/**
 * Represents a rectangular area in the terminal.
 *
 * @immutable
 */
final readonly class Rect
{
    public function __construct(
        public int $x,
        public int $y,
        public int $width,
        public int $height,
    ) {}

    /**
     * Create a rect at origin (0, 0) with the given dimensions.
     */
    public static function fromSize(int $width, int $height): self
    {
        return new self(0, 0, $width, $height);
    }

    /**
     * Returns the total number of cells in this rect.
     */
    public function area(): int
    {
        return $this->width * $this->height;
    }

    /**
     * Returns the right edge x-coordinate (exclusive).
     */
    public function right(): int
    {
        return $this->x + $this->width;
    }

    /**
     * Returns the bottom edge y-coordinate (exclusive).
     */
    public function bottom(): int
    {
        return $this->y + $this->height;
    }

    /**
     * Returns true if this rect has zero area.
     */
    public function isEmpty(): bool
    {
        return $this->width === 0 || $this->height === 0;
    }

    /**
     * Returns a new rect inset by the given margins.
     *
     * @param int<0, max> $top
     * @param int<0, max> $right
     * @param int<0, max> $bottom
     * @param int<0, max> $left
     */
    public function inner(int $top = 0, int $right = 0, int $bottom = 0, int $left = 0): self
    {
        $newX = Math\minva($this->x + $left, $this->right());
        $newY = Math\minva($this->y + $top, $this->bottom());
        $newWidth = Math\maxva(0, $this->width - $left - $right);
        $newHeight = Math\maxva(0, $this->height - $top - $bottom);

        return new self($newX, $newY, $newWidth, $newHeight);
    }

    /**
     * Returns true if the given coordinates are inside this rect.
     */
    public function contains(int $x, int $y): bool
    {
        return $x >= $this->x && $x < $this->right() && $y >= $this->y && $y < $this->bottom();
    }
}
