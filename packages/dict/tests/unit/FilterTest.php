<?php

declare(strict_types=1);

namespace Psl\Dict\Tests\Unit;

use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Dict;
use Psl\Iter;

final class FilterTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testFilter(array $expected, array $array, null|Closure $predicate = null): void
    {
        $result = Dict\filter($array, $predicate);

        static::assertSame($expected, $result);
    }

    public static function provideData(): iterable
    {
        yield [[], []];
        yield [['a', 'b'], ['a', 'b']];
        yield [[], ['a', 'b'], static fn(): bool => false];
        yield [['a', 'b'], ['a', 'b'], static fn(string $_): bool => true];
        yield [['a'], ['a', 'b'], static fn(string $v): bool => 'b' !== $v];
    }

    public function testFilterWithNonArrayIterable(): void
    {
        $iterator = Iter\Iterator::create(['a' => 1, 'b' => 0, 'c' => 3]);

        static::assertSame(['a' => 1, 'c' => 3], Dict\filter($iterator));
    }

    public function testFilterWithNonArrayIterableAndPredicate(): void
    {
        $iterator = Iter\Iterator::create(['a' => 1, 'b' => 2, 'c' => 3]);

        static::assertSame(['b' => 2, 'c' => 3], Dict\filter($iterator, static fn(int $v): bool => $v > 1));
    }
}
