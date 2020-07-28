<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;

class FirstKeyTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFirstKey($expected, iterable $iterable): void
    {
        $result = Iter\first_key($iterable);

        self::assertSame($expected, $result);
    }

    public function provideData(): iterable
    {
        yield [null, []];
        yield [null, new \SplDoublyLinkedList()];
        yield ['a', ['a' => 'b']];
        yield [0, ['a', 'b']];
        yield [0, new Collection\Vector(['a', 'b'])];
        yield [0, new Collection\Vector(['a' => 'b'])];
        yield ['a', new Collection\Map(['a' => 'b'])];
        yield [null, (static function () {
            yield null => null;
        })()];
        yield [null, (static function () {
            return;
            yield;
        })()];
    }
}
