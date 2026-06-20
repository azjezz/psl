<?php

declare(strict_types=1);

namespace Psl\Iter\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;

final class ContainsTest extends TestCase
{
    /**
     * @param iterable<mixed> $iterable
     * @param mixed $value
     */
    #[DataProvider('provideData')]
    public function testContainsKey(bool $expected, iterable $iterable, null|int|string $value): void
    {
        static::assertSame($expected, Iter\contains::<int|string|null>($iterable, $value));
    }

    public static function provideData(): iterable
    {
        yield [false, [], 0];
        yield [false, [], 1];
        yield [false, [], null];
        yield [false, [0], null];
        yield [true, [null], null];
        yield [false, [1, 2], 0];
        yield [true, [1, 2], 1];
        yield [true, [1, 2], 2];
        yield [false, ['hello' => 'world'], 'hello'];
        yield [true, ['hello' => 'world'], 'world'];
        yield [false, ['hello' => 'world'], 'worlD'];
        yield [true, ['' => ''], ''];
        yield [false, new Collection\Vector::<int>([1, 2]), 0];
        yield [true, new Collection\Vector::<int>([1, 2]), 1];
        yield [true, new Collection\Vector::<int>([1, 2]), 2];
        yield [false, (static fn(): iterable => yield 'foo' => 'bar')(), 'foo'];
        yield [true, (static fn(): iterable => yield 'foo' => 'bar')(), 'bar'];
    }
}
