<?php

declare(strict_types=1);

namespace Psl\Iter\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class ReduceKeysTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testReduceKeys(
        null|int $expected,
        iterable $iterable,
        callable $function,
        null|int $initial = null,
    ): void {
        static::assertSame($expected, Iter\reduce_keys($iterable, $function, $initial));
    }

    public static function provideData(): iterable
    {
        yield [null, [], static fn(null $accumulator, int $_): null => $accumulator, null];
        yield [3, [1, 2, 3], static fn(int $accumulator, int $k): int => $accumulator + $k, 0];
        yield [3, Iter\to_iterator([1, 2, 3]), static fn(int $accumulator, int $k): int => $accumulator + $k, 0];
    }
}
