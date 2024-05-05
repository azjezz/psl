<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;
use SplDoublyLinkedList;

final class LastOptTest extends TestCase
{
    /**
     * @dataProvider provideDataSome
     */
    public function testLastSome($expected, iterable $iterable): void
    {
        $result = Iter\last_opt($iterable);

        static::assertSame($expected, $result->unwrap());
    }

    public function provideDataSome(): iterable
    {
        yield ['d', ['a' => 'b', 'c' => 'd']];
        yield ['b', ['a', 'b']];
        yield ['b', new Collection\Vector(['a', 'b'])];
        yield ['b', new Collection\Vector(['b'])];
        yield ['d', new Collection\Map(['a' => 'b', 'c' => 'd'])];
        yield [null, (static function () {
            yield null => null;
        })()];
    }

    /**
     * @dataProvider provideDataNone
     */
    public function testLastNone(iterable $iterable): void
    {
        $result = Iter\last_opt($iterable);

        static::assertTrue($result->isNone());
    }

    public function provideDataNone(): iterable
    {
        yield [ []];
        yield [ new SplDoublyLinkedList()];
        yield [ (static function () {
            return;
            yield;
        })()];
    }
}
