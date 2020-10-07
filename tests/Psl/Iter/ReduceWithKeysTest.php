<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

class ReduceWithKeysTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testReduceWithKeys($expected, iterable $iterable, callable $function, $initial = null): void
    {
        self::assertSame($expected, Iter\reduce_with_keys($iterable, $function, $initial));
    }

    public function provideData(): iterable
    {
        yield [null, [], fn ($accumulator, $k, $v) => $accumulator, null];
        yield [6, [1, 2, 3], fn ($accumulator, $k, $v) => $accumulator + $v, 0];
        yield [6, Iter\to_iterator([1, 2, 3]), fn ($accumulator, $k, $v) => $accumulator + $v, 0];
    }
}
