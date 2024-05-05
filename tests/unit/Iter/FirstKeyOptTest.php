<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;
use SplDoublyLinkedList;

final class FirstKeyOptTest extends TestCase
{
    /**
     * @dataProvider provideDataSome
     */
    public function testFirstKeyOptSome($expected, iterable $iterable): void
    {
        $result = Iter\first_key_opt($iterable);

        static::assertSame($expected, $result->unwrap());
    }

    public function provideDataSome(): iterable
    {
        yield ['a', ['a' => 'b']];
        yield [0, ['a', 'b']];
        yield [0, new Collection\Vector(['a', 'b'])];
        yield [0, new Collection\Vector(['a' => 'b'])];
        yield ['a', new Collection\Map(['a' => 'b'])];
        yield [null, (static function () {
            yield null => null;
        })()];
    }

    /**
     * @dataProvider provideDataNone
     */
    public function testFirstKeyOptNone(iterable $iterable): void
    {
        $result = Iter\first_key_opt($iterable);

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
