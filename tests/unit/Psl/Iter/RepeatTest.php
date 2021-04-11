<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Iter;
use Psl\Math;

final class RepeatTest extends TestCase
{
    public function testRepeat(): void
    {
        $result = Iter\repeat(42, 5);
        
        static::assertSame([42, 42, 42, 42, 42], Iter\to_array($result));
    }

    public function testRepeatThrowsIfNumIsNegative(): void
    {
        $this->expectException(Psl\Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Number of repetitions must be non-negative.');

        Iter\repeat(4, -1);
    }

    public function testRepeatToInfinityIfNumIsNotProvided(): void
    {
        $result = Iter\repeat('hello');
        $result->seek((int) Math\INFINITY);

        static::assertTrue($result->valid());
        static::assertSame('hello', $result->current());
        static::assertSame((int) Math\INFINITY, $result->key());
    }
}
