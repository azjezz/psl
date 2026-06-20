<?php

declare(strict_types=1);

namespace Psl\Iter\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;
use SplDoublyLinkedList;

final class LastTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testLast(null|string $expected, iterable $iterable): void
    {
        $result = Iter\last::<string|null>($iterable);

        static::assertSame($expected, $result);
    }

    public static function provideData(): iterable
    {
        yield [null, []];
        yield [null, new SplDoublyLinkedList()];
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
        yield [
            null,
            (static function (): iterable {
                return;
                yield;
            })(),
        ];
    }
}
