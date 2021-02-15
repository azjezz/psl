<?php

declare(strict_types=1);

namespace Psl\Tests\Dict;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Dict;

final class TakeTest extends TestCase
{
    public function testTake(): void
    {
        $result = Dict\take([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 3);

        static::assertSame([-5, -4, -3], $result);
    }

    public function testTakeThrowsIfLengthIsNegative(): void
    {
        $this->expectException(Psl\Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Length must be non-negative.');

        Dict\take([1, 2, 3], -3);
    }
}
