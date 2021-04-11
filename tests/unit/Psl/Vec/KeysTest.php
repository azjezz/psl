<?php

declare(strict_types=1);

namespace Psl\Tests\Vec;

use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Vec;

final class KeysTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testKeys(array $expected, iterable $iterable): void
    {
        $result = Vec\keys($iterable);

        static::assertSame($expected, Iter\to_array_with_keys($result));
    }

    public function provideData(): iterable
    {
        yield [[0, 1, 2, 3], [1, 2, 3, 4]];
        yield [[0, 1, 2, 3], Iter\to_iterator([1, 2, 3, 4])];
        yield [[0, 1, 2, 3], Vec\range(1, 4)];
        yield [[0, 1, 2, 3, 4], Vec\range(4, 8)];
        yield [[], []];
        yield [[0], [null]];
        yield [[0, 1], [null, null]];
    }
}
