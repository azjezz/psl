<?php

declare(strict_types=1);

namespace Psl\Vec\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Vec;

final class EnumerateTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testEnumerate(array $expected, iterable $iterable): void
    {
        static::assertSame($expected, Vec\enumerate($iterable));
    }

    public static function provideData(): iterable
    {
        yield [[], []];
        yield [[['a', 'b'], ['c', 'd']], ['a' => 'b', 'c' => 'd']];
        yield [
            [['a', 'b'], ['a', 'b'], ['a', 'b']],
            (static function (): iterable {
                yield 'a' => 'b';
                yield 'a' => 'b';
                yield 'a' => 'b';
            })(),
        ];
        yield [[['a', null], ['b', 0]], ['a' => null, 'b' => 0]];
    }
}
