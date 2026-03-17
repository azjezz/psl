<?php

declare(strict_types=1);

namespace Psl\Terminal;

/**
 * Window size provider that returns a fixed size.
 *
 * Useful for remote scenarios where the initial size is known,
 * and for testing.
 */
final readonly class StaticWindowSizeProvider implements WindowSizeProviderInterface
{
    /**
     * @param int $width Number of columns.
     * @param int $height Number of rows.
     */
    public function __construct(
        private int $width,
        private int $height,
    ) {}

    public function get(): array
    {
        return [$this->width, $this->height];
    }
}
