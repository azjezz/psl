<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Arr;

class SliceTest extends TestCase
{
    public function testSlice(): void
    {
        $result = Arr\slice([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5);

        self::assertSame([0, 1, 2, 3, 4, 5], $result);
    }

    public function testSliceWithLength(): void
    {
        $result = Arr\slice([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5, 3);

        self::assertSame([0, 1, 2], $result);
    }

    public function testSliceWithZeroLength(): void
    {
        $result = Arr\slice([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5, 0);

        self::assertSame([], $result);
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
