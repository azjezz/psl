<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Vec;

final class LastKeyTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testLastKey($expected, iterable $iterable): void
    {
        $result = Iter\last_key($iterable);

        static::assertSame($expected, $result);
    }

    public function provideData(): iterable
    {
        yield [3, [1, 2, 3, 4]];
        yield [3, Iter\to_iterator([1, 2, 3, 4])];
        yield [3, Vec\range(1, 4)];
        yield [4, Vec\range(4, 8)];
        yield [4, Iter\to_iterator(Vec\range(4, 8))];
        yield [null, []];
        yield [0, [null]];
        yield [1, [null, null]];
        yield [[1, 2], (static fn(): iterable => yield [1, 2] => 'hello')()];
    }
}
