<?php

declare(strict_types=1);

namespace Psl\Vec\Tests\Unit;

use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Vec;

final class FilterTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testFilter(array $expected, array $array, null|Closure $predicate = null): void
    {
        $result = Vec\filter($array, $predicate);

        static::assertSame($expected, $result);
    }

    public static function provideData(): iterable
    {
        yield [[], []];
        yield [['a', 'b'], ['a', 'b']];
        yield [[], ['a', 'b'], static fn(): false => false];
        yield [['a', 'b'], ['a', 'b'], static fn(string $_): bool => true];
        yield [['a'], ['a', 'b'], static fn(string $v): bool => 'b' !== $v];
    }

    public function testFilterWithNonArrayIterable(): void
    {
        $iterator = Iter\Iterator::create([1, 0, 3]);

        static::assertSame([1, 3], Vec\filter($iterator));
    }

    public function testFilterWithNonArrayIterableAndPredicate(): void
    {
        $iterator = Iter\Iterator::create([1, 2, 3]);

        static::assertSame([2, 3], Vec\filter($iterator, static fn(int $v): bool => $v > 1));
    }
}
