<?php

declare(strict_types=1);

namespace Psl\Vec\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Vec;

final class ValuesTest extends TestCase
{
    #[DataProvider('provideTestValues')]
    public function testValues(array $expected, iterable $iterable): void
    {
        static::assertSame($expected, Vec\values::<mixed>($iterable));
    }

    public static function provideTestValues(): iterable
    {
        yield [[], []];
        yield [[null], [null]];
        yield [[1, 2], [1, 2]];
        yield [[1, 2, 3, 4, 5], Vec\range::<int>(1, 5)];
        yield [['hello', 'world'], new Collection\Map::<string, string>(['foo' => 'hello', 'bar' => 'world'])];
        yield [['foo', 'bar'], new Collection\Vector::<string>(['foo', 'bar'])];
    }
}
