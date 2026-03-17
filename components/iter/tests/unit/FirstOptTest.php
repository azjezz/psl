<?php

declare(strict_types=1);

namespace Psl\Iter\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;
use SplDoublyLinkedList;

final class FirstOptTest extends TestCase
{
    #[DataProvider('provideDataSome')]
    public function testFirstSome(null|string $expected, iterable $iterable): void
    {
        $result = Iter\first_opt($iterable);

        static::assertSame($expected, $result->unwrap());
    }

    public static function provideDataSome(): iterable
    {
        yield ['b', ['a' => 'b', 'c' => 'd']];
        yield ['a', ['a', 'b']];
        yield ['a', new Collection\Vector(['a', 'b'])];
        yield ['b', new Collection\Vector(['b'])];
        yield ['b', new Collection\Map(['a' => 'b', 'c' => 'd'])];
        yield [
            null,
            (static function (): iterable {
                yield null => null;
            })(),
        ];
    }

    #[DataProvider('provideDataNone')]
    public function testFirstNone(iterable $iterable): void
    {
        $result = Iter\first_opt($iterable);

        static::assertTrue($result->isNone());
    }

    public static function provideDataNone(): iterable
    {
        yield [[]];
        yield [new SplDoublyLinkedList()];
        yield [(static function (): iterable {
            return;
            yield;
        })()];
    }
}
