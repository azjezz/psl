<?php

declare(strict_types=1);

namespace Psl\Tests\Vec;

use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Vec;

final class ReductionsTest extends TestCase
{
    /**
     * @template Tk
     * @template Tv
     * @template Ts
     *
     * @param iterable<Tk, Tv> $iterable
     * @param (callable(Ts, Tk, Tv): Ts) $function
     * @param Ts $initial
     *
     * @dataProvider provideData
     */
    public function testReductions(array $expected, iterable $iterable, callable $function, $initial): void
    {
        $result = Vec\reductions($iterable, $function, $initial);

        static::assertSame($expected, Iter\to_array_with_keys($result));
    }

    public function provideData(): iterable
    {
        yield [[], [], static fn ($accumulator, $k, $v) => $accumulator, null];
        yield [[1, 3, 6], [1, 2, 3], static fn ($accumulator, $k, $v) => $accumulator + $v, 0];
        yield [[1, 3, 6], Iter\to_iterator([1, 2, 3]), static fn ($accumulator, $k, $v) => $accumulator + $v, 0];
    }
}
