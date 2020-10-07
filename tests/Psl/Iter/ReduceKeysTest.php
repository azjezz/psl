<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class ReduceKeysTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testReduceKeys($expected, iterable $iterable, callable $function, $initial = null): void
    {
        self::assertSame($expected, Iter\reduce_keys($iterable, $function, $initial));
    }

    public function provideData(): iterable
    {
        yield [null, [], fn ($accumulator, $k) => $accumulator, null];
        yield [3, [1, 2, 3], fn ($accumulator, $k) => $accumulator + $k, 0];
        yield [3, Iter\to_iterator([1, 2, 3]), fn ($accumulator, $k) => $accumulator + $k, 0];
    }
}
