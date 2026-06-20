<?php

declare(strict_types=1);

namespace Psl\Iter\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;
use SplDoublyLinkedList;

final class FirstKeyTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testFirstKey(null|int|string $expected, iterable $iterable): void
    {
        $result = Iter\first_key::<string|int|null, string|null>($iterable);

        static::assertSame($expected, $result);
    }

    public static function provideData(): iterable
    {
        yield [null, []];
        yield [null, new SplDoublyLinkedList()];
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
        yield [
            null,
            (static function (): iterable {
                return;
                yield;
            })(),
        ];
    }
}
