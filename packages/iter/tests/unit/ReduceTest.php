<?php

declare(strict_types=1);

namespace Psl\Iter\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class ReduceTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testReduce(
        null|int $expected,
        iterable $iterable,
        callable $function,
        null|int $initial = null,
    ): void {
        static::assertSame($expected, Iter\reduce::<int, null|int>($iterable, $function, $initial));
    }

    public static function provideData(): iterable
    {
        yield [null, [], static fn(null $accumulator, int $_): null => $accumulator, null];
        yield [6, [1, 2, 3], static fn(int $accumulator, int $v): int => $accumulator + $v, 0];
        yield [6, Iter\to_iterator::<int, int>([1, 2, 3]), static fn(int $accumulator, int $v): int => $accumulator + $v, 0];
    }
}
