<?php

declare(strict_types=1);

namespace Psl\Iter\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;
use SplDoublyLinkedList;

final class FirstKeyOptTest extends TestCase
{
    #[DataProvider('provideDataSome')]
    public function testFirstKeyOptSome(mixed $expected, iterable $iterable): void
    {
        $result = Iter\first_key_opt::<string|int|null, string|null>($iterable);

        static::assertSame($expected, $result->unwrap());
    }

    public static function provideDataSome(): iterable
    {
        yield ['a', ['a' => 'b']];
        yield [0, ['a', 'b']];
        yield [0, new Collection\Vector::<string>(['a', 'b'])];
        yield [0, new Collection\Vector::<string>(['a' => 'b'])];
        yield ['a', new Collection\Map::<string, string>(['a' => 'b'])];
        yield [
            null,
            (static function (): iterable {
                yield null => null;
            })(),
        ];
    }

    #[DataProvider('provideDataNone')]
    public function testFirstKeyOptNone(iterable $iterable): void
    {
        $result = Iter\first_key_opt::<string|int|null, string|null>($iterable);

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
