<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Iter;

class SliceTest extends TestCase
{
    public function testSlice(): void
    {
        $result = Iter\slice([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5);
        
        self::assertSame([0, 1, 2, 3, 4, 5], Iter\to_array($result));
    }

    public function testSliceWithLength(): void
    {
        $result = Iter\slice([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5, 3);

        self::assertSame([0, 1, 2], Iter\to_array($result));
    }

    public function testSliceWithZeroLength(): void
    {
        $result = Iter\slice([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5, 0);

        self::assertSame([], Iter\to_array($result));
    }

    public function testSliceThrowsIfStartIsNegative(): void
    {
        $this->expectException(Psl\Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Start offset must be non-negative.');

        Iter\slice([1, 2, 3], -3);
    }

    public function testSliceThrowsIfLengthIsNegative(): void
    {
        $this->expectException(Psl\Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Length must be non-negative.');

        Iter\slice([1, 2, 3], 1, -3);
    }
}
