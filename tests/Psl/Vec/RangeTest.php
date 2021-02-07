<?php

declare(strict_types=1);

namespace Psl\Tests\Vec;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Vec;

final class RangeTest extends TestCase
{
    public function testRange(): void
    {
        static::assertSame([1], Vec\values(Vec\range(1, 1, 1)));
        static::assertSame([0, 1, 2, 3, 4, 5], Vec\values(Vec\range(0, 5)));
        static::assertSame([5, 4, 3, 2, 1, 0], Vec\values(Vec\range(5, 0)));
        static::assertSame([0.0, 0.5, 1.0, 1.5, 2.0, 2.5, 3.0], Vec\values(Vec\range(0.0, 3.0, 0.5)));
        static::assertSame([3.0, 2.5, 2.0, 1.5, 1.0, 0.5, 0.0], Vec\values(Vec\range(3.0, 0.0, -0.5)));
    }

    public function testRandomThrowsIfEndIsGreaterThanStartAndStepIsNegative(): void
    {
        $this->expectException(Psl\Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('If $start < $end, then $step must be positive or null.');

        Vec\range(0, 10, -2);
    }

    public function testRandomThrowsIfStartIsGreaterThanEndAndStepIsPositive(): void
    {
        $this->expectException(Psl\Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('If $start > $end, then $step must be negative or null.');

        Vec\range(20, 10, 2);
    }
}
