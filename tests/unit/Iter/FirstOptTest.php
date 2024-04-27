<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;
use SplDoublyLinkedList;

final class FirstOptTest extends TestCase
{
    /**
     * @dataProvider provideDataSome
     */
    public function testFirstSome($expected, iterable $iterable): void
    {
        $result = Iter\first_opt($iterable);

        static::assertSame($expected, $result->unwrap());
    }

    public function provideDataSome(): iterable
    {
        yield ['b', ['a' => 'b', 'c' => 'd']];
        yield ['a', ['a', 'b']];
        yield ['a', new Collection\Vector(['a', 'b'])];
        yield ['b', new Collection\Vector(['b'])];
        yield ['b', new Collection\Map(['a' => 'b', 'c' => 'd'])];
        yield [null, (static function () {
            yield null => null;
        })()];
    }

    /**
     * @dataProvider provideDataNone
     */
    public function testFirstNone(iterable $iterable): void
    {
        $result = Iter\first_opt($iterable);

        static::assertTrue($result->isNone());
    }

    public function provideDataNone(): iterable
    {
        yield [[]];
        yield [new SplDoublyLinkedList()];
        yield [(static function () {
            return;
            yield;
        })()];
    }
}
