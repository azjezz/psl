<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;
use SplDoublyLinkedList;

final class LastTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testLast($expected, iterable $iterable): void
    {
        $result = Iter\last($iterable);

        static::assertSame($expected, $result);
    }

    public function provideData(): iterable
    {
        yield [null, []];
        yield [null, new SplDoublyLinkedList()];
        yield ['d', ['a' => 'b', 'c' => 'd']];
        yield ['b', ['a', 'b']];
        yield ['b', new Collection\Vector(['a', 'b'])];
        yield ['b', new Collection\Vector(['b'])];
        yield ['d', new Collection\Map(['a' => 'b', 'c' => 'd'])];
        yield [
            null,
            (static function () {
                yield null => null;
            })(),
        ];
        yield [
            null,
            (static function () {
                return;
                yield;
            })(),
        ];
    }
}
