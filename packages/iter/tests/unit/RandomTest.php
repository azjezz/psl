<?php

declare(strict_types=1);

namespace Psl\Iter\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class RandomTest extends TestCase
{
    public function testRandom(): void
    {
        $iterable = [1, 2, 3, 4, 5];
        $value = Iter\random::<int>($iterable);

        static::assertTrue(Iter\contains::<int>($iterable, $value));

        $iterable = Iter\to_iterator::<int, int>([1, 2, 3, 4, 5]);
        $value = Iter\random::<int>($iterable);

        static::assertTrue(Iter\contains::<int>($iterable, $value));

        $value = Iter\random::<int>([1]);

        static::assertSame(1, $value);
    }

    public function testRandomWithEmptyIterator(): void
    {
        $this->expectException(Iter\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected a non-empty iterable.');

        Iter\random::<int>([]);
    }
}
