<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;

class FirstTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFirst($expected, iterable $iterable): void
    {
        $result = Iter\first($iterable);

        self::assertSame($expected, $result);
    }

    public function provideData(): iterable
    {
        yield [null, []];
        yield [null, new \SplDoublyLinkedList()];
        yield ['b', ['a' => 'b', 'c' => 'd']];
        yield ['a', ['a', 'b']];
        yield ['a', new Collection\Vector(['a', 'b'])];
        yield ['b', new Collection\Vector(['b'])];
        yield ['b', new Collection\Map(['a' => 'b', 'c' => 'd'])];
        yield [null, (static function () {
            yield null => null;
        })()];
        yield [null, (static function () {
            return;
            yield;
        })()];
    }
}
