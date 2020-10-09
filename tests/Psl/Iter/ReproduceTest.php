<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Exception\InvariantViolationException;
use Psl\Fun;
use Psl\Iter;
use Psl\Math;

final class ReproduceTest extends TestCase
{
    public function testReproduce(): void
    {
        self::assertSame([1], Iter\to_array((Iter\reproduce(Fun\identity(), 1))));
        self::assertSame([1, 2, 3], Iter\to_array(Iter\reproduce(Fun\identity(), 3)));
    }

    public function testThrowsIfNumberIsLowerThan1(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('The number of times you want to reproduct must be at least 1.');

        Iter\reproduce(Fun\identity(), 0);
    }

    public function testReproduceToInfinityIfNumIsNotProvided(): void
    {
        $result = Iter\reproduce(static fn () => 'hello');
        $result->seek((int) Math\INFINITY);

        self::assertTrue($result->valid());
        self::assertSame('hello', $result->current());
        self::assertSame((int) Math\INFINITY, $result->key());
    }
}
