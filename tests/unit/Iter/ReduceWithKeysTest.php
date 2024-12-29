<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class ReduceWithKeysTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testReduceWithKeys($expected, iterable $iterable, callable $function, $initial = null): void
    {
        static::assertSame($expected, Iter\reduce_with_keys($iterable, $function, $initial));
    }

    public function provideData(): iterable
    {
        yield [null, [], static fn($accumulator, $_k, $_v) => $accumulator, null];
        yield [6, [1, 2, 3], static fn($accumulator, $_k, $v) => $accumulator + $v, 0];
        yield [6, Iter\to_iterator([1, 2, 3]), static fn($accumulator, $_k, $v) => $accumulator + $v, 0];
    }
}
