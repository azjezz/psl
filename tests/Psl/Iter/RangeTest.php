<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Iter;

class RangeTest extends TestCase
{
    public function testRange(): void
    {
        self::assertSame([1], Iter\to_array(Iter\range(1, 1, 1)));
        self::assertSame([0, 1, 2, 3, 4, 5], Iter\to_array(Iter\range(0, 5)));
        self::assertSame([5, 4, 3, 2, 1, 0], Iter\to_array(Iter\range(5, 0)));
        self::assertSame([0.0, 0.5, 1.0, 1.5, 2.0, 2.5, 3.0], Iter\to_array(Iter\range(0.0, 3.0, 0.5)));
        self::assertSame([3.0, 2.5, 2.0, 1.5, 1.0, 0.5, 0.0], Iter\to_array(Iter\range(3.0, 0.0, -0.5)));
    }

    public function testRandomThrowsIfEndIsGreaterThanStartAndStepIsNegative(): void
    {
        $this->expectException(Psl\Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('If start < end, the step must be positive.');

        Iter\range(0, 10, -2);
    }

    public function testRandomThrowsIfStartIsGreaterThanEndAndStepIsPositive(): void
    {
        $this->expectException(Psl\Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('If start > end, the step must be negative.');

        Iter\range(20, 10, 2);
    }
}
