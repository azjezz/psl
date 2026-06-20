<?php

declare(strict_types=1);

namespace Psl\Iter\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;
use SplDoublyLinkedList;

final class LastOptTest extends TestCase
{
    #[DataProvider('provideDataSome')]
    public function testLastSome(null|string $expected, iterable $iterable): void
    {
        $result = Iter\last_opt::<string|null>($iterable);

        static::assertSame($expected, $result->unwrap());
    }

    public static function provideDataSome(): iterable
    {
        yield ['d', ['a' => 'b', 'c' => 'd']];
        yield ['b', ['a', 'b']];
        yield ['b', new Collection\Vector::<string>(['a', 'b'])];
        yield ['b', new Collection\Vector::<string>(['b'])];
        yield ['d', new Collection\Map::<string, string>(['a' => 'b', 'c' => 'd'])];
        yield [
            null,
            (static function (): iterable {
                yield null => null;
            })(),
        ];
    }

    #[DataProvider('provideDataNone')]
    public function testLastNone(iterable $iterable): void
    {
        $result = Iter\last_opt::<string|null>($iterable);

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
