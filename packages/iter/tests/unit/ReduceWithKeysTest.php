<?php

declare(strict_types=1);

namespace Psl\Iter\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class ReduceWithKeysTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testReduceWithKeys(
        null|int $expected,
        iterable $iterable,
        callable $function,
        null|int $initial = null,
    ): void {
        static::assertSame($expected, Iter\reduce_with_keys::<int, int, null|int>($iterable, $function, $initial));
    }

    public static function provideData(): iterable
    {
        yield [null, [], static fn(null $accumulator, int $_k, int $_v): null => $accumulator, null];
        yield [6, [1, 2, 3], static fn(int $accumulator, int $_k, int $v): int => $accumulator + $v, 0];
        yield [
            6,
            Iter\to_iterator::<int, int>([1, 2, 3]),
            static fn(int $accumulator, int $_k, int $v): int => $accumulator + $v,
            0,
        ];
    }
}
