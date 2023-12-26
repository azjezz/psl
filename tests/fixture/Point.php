<?php

declare(strict_types=1);

namespace Psl\Tests\Fixture;

final class Point
{
    public function __construct(
        public readonly int $x,
        public readonly int $y,
    ) {
    }
}
