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
        static::assertSame($expected, Vec\values($iterable));
    }

    public static function provideTestValues(): iterable
    {
        yield [[], []];
        yield [[null], [null]];
        yield [[1, 2], [1, 2]];
        yield [[1, 2, 3, 4, 5], Vec\range(1, 5)];
        yield [['hello', 'world'], new Collection\Map(['foo' => 'hello', 'bar' => 'world'])];
        yield [['foo', 'bar'], new Collection\Vector(['foo', 'bar'])];
    }
}
