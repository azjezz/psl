<?php

declare(strict_types=1);

namespace Psl\Iter\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;

final class ContainsKeyTest extends TestCase
{
    /**
     * @param iterable<mixed, mixed> $iterable
     * @param mixed $key
     */
    #[DataProvider('provideData')]
    public function testContainsKey(bool $expected, iterable $iterable, mixed $key): void
    {
        static::assertSame($expected, Iter\contains_key::<string|int, int|string>($iterable, $key));
    }

    public static function provideData(): iterable
    {
        yield [false, [], 0];
        yield [false, [], 1];
        yield [true, [1, 2], 0];
        yield [true, [1, 2], 1];
        yield [false, [1, 2], 2];
        yield [true, ['hello' => 'world'], 'hello'];
        yield [false, ['hello' => 'world'], 'hellO'];
        yield [false, ['hello' => 'world'], 'world'];
        yield [false, ['' => ''], null];
        yield [true, new Collection\Vector::<int>([1, 2]), 0];
        yield [true, new Collection\Vector::<int>([1, 2]), 1];
        yield [false, new Collection\Vector::<int>([1, 2]), 2];
        yield [
            true,
            (static fn(): iterable => yield 'foo' => 'bar')(),
            'foo',
        ];
        yield [
            false,
            (static fn(): iterable => yield 'foo' => 'bar')(),
            'bar',
        ];
    }
}
