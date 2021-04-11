<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Arr;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Arr;

final class SliceTest extends TestCase
{
    public function testSlice(): void
    {
        $result = Arr\slice([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5);

        static::assertSame([5 => 0, 6 => 1, 7 => 2, 8 => 3, 9 => 4, 10 => 5], $result);
    }

    public function testSliceWithLength(): void
    {
        $result = Arr\slice([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5, 3);

        static::assertSame([5 => 0, 6 => 1, 7 => 2], $result);
    }

    public function testSliceWithZeroLength(): void
    {
        $result = Arr\slice([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5, 0);

        static::assertSame([], $result);
    }

    public function testSliceThrowsIfStartIsNegative(): void
    {
        $this->expectException(Psl\Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Start offset must be non-negative.');

        Arr\slice([1, 2, 3], -3);
    }

    public function testSliceThrowsIfLengthIsNegative(): void
    {
        $this->expectException(Psl\Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Length must be non-negative.');

        Arr\slice([1, 2, 3], 1, -3);
    }
}
