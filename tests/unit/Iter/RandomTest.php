<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class RandomTest extends TestCase
{
    public function testRandom(): void
    {
        $iterable = [1, 2, 3, 4, 5];
        $value = Iter\random($iterable);

        static::assertTrue(Iter\contains($iterable, $value));

        $iterable = Iter\to_iterator([1, 2, 3, 4, 5]);
        $value = Iter\random($iterable);

        static::assertTrue(Iter\contains($iterable, $value));

        $value = Iter\random([1]);

        static::assertSame(1, $value);
    }

    public function testRandomWithEmptyIterator(): void
    {
        $this->expectException(Iter\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a non-empty iterable.');

        Iter\random([]);
    }
}
