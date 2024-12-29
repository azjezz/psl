<?php

declare(strict_types=1);

namespace Psl\Tests\Fixture;

final readonly class Point
{
    public function __construct(
        public int $x,
        public int $y,
    ) {
    }
}
