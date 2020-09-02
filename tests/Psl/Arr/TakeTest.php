<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Arr;

class TakeTest extends TestCase
{
    public function testTake(): void
    {
        $result = Arr\take([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 3);

        self::assertSame([-5, -4, -3], $result);
    }

    public function testTakeThrowsIfLengthIsNegative(): void
    {
        $this->expectException(Psl\Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Length must be non-negative.');

        Arr\take([1, 2, 3], -3);
    }
}
