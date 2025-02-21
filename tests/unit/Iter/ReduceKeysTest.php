<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class ReduceKeysTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testReduceKeys($expected, iterable $iterable, callable $function, $initial = null): void
    {
        static::assertSame($expected, Iter\reduce_keys($iterable, $function, $initial));
    }

    public function provideData(): iterable
    {
        yield [null, [], static fn(null $accumulator, int $_k): null => $accumulator, null];
        yield [3, [1, 2, 3], static fn(int $accumulator, int $k): int => $accumulator + $k, 0];
        yield [3, Iter\to_iterator([1, 2, 3]), static fn(int $accumulator, int $k): int => $accumulator + $k, 0];
    }
}
