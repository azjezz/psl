<?php

declare(strict_types=1);

namespace Psl\Vec\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Vec;

final class ReductionsTest extends TestCase
{
    /**
     * @param iterable<mixed, mixed> $iterable
     * @param (callable(mixed, mixed, mixed): mixed) $function
     * @param mixed $initial
     */
    #[DataProvider('provideData')]
    public function testReductions(array $expected, iterable $iterable, callable $function, mixed $initial): void
    {
        static::assertSame($expected, Vec\reductions::<int, int, int>($iterable, $function, $initial));
    }

    /**
     * @return iterable<array{0: list<int>, 1: iterable<int>, 2: (Closure(int, int, int): int)}>
     */
    public static function provideData(): iterable
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
            Iter\to_iterator::<int, int>([1, 2, 3]),
            static fn(int $accumulator, int $_k, int $v): int => $accumulator + $v,
            0,
        ];
    }
}
