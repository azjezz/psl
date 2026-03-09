<?php

declare(strict_types=1);

namespace Psl\Terminal\Widget;

/**
 * A value object representing padding on all four sides.
 *
 * @immutable
 */
final readonly class Padding
{
    /**
     * @param non-negative-int $top
     * @param non-negative-int $right
     * @param non-negative-int $bottom
     * @param non-negative-int $left
     */
    public function __construct(
        public int $top = 0,
        public int $right = 0,
        public int $bottom = 0,
        public int $left = 0,
    ) {}
}
