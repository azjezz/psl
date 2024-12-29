<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Vec;

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
    public function testReductions(array $expected, iterable $iterable, callable $function, mixed $initial): void
    {
        static::assertSame($expected, Vec\reductions($iterable, $function, $initial));
    }

    /**
     * @return iterable<array{0: list<int>, 1: iterable<int>, 2: (function(int, int, int): int)}>
     */
    public function provideData(): iterable
    {
        yield [
            [],
            [],
            static fn(int $accumulator, int $_k, int $_v): int => $accumulator,
            0,
        ];

        yield [
            [1, 3, 6],
            [1, 2, 3],
            static fn(int $accumulator, int $_k, int $v): int => $accumulator + $v,
            0,
        ];

        yield [
            [1, 3, 6],
            Iter\to_iterator([1, 2, 3]),
            static fn(int $accumulator, int $_k, int $v): int => $accumulator + $v,
            0,
        ];
    }
}
