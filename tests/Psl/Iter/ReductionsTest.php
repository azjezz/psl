<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

class ReductionsTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testReductions(array $expected, iterable $iterable, callable $function, $initial = null): void
    {
        $result = Iter\reductions($iterable, $function, $initial);

        self::assertSame($expected, Iter\to_array_with_keys($result));
    }

    public function provideData(): iterable
    {
        yield [[], [], fn ($accumulator, $k, $v) => $accumulator, null];
        yield [[1, 3, 6], [1, 2, 3], fn ($accumulator, $k, $v) => $accumulator + $v, 0];
        yield [[1, 3, 6], Iter\to_iterator([1, 2, 3]), fn ($accumulator, $k, $v) => $accumulator + $v, 0];
    }
}
