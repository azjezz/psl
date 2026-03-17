<?php

declare(strict_types=1);

namespace Psl\Vec\Tests\Unit;

use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Vec;

final class FilterKeysTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testFilter(array $expected, iterable $iterable, null|Closure $predicate = null): void
    {
        $result = Vec\filter_keys($iterable, $predicate);

        static::assertSame($expected, $result);
    }

    public static function provideData(): iterable
    {
        yield [[], []];
        yield [['b'], ['a', 'b']];
        yield [['a'], ['a', 'b'], static fn(int $k): bool => 1 !== $k];
        yield [['b'], ['a', 'b'], static fn(int $k): bool => 0 !== $k];
        yield [['b'], Collection\Vector::fromArray(['a', 'b']), static fn(int $k): bool => 0 !== $k];
        yield [[], Collection\Vector::fromArray(['a', 'b']), static fn(int $_): bool => false];
        yield [[], Collection\Vector::fromArray([]), static fn(int $_): bool => false];
        yield [[], ['a', 'b'], static fn(int $_): bool => false];
        yield [['a', 'b'], ['a', 'b'], static fn(int $_): bool => true];
    }
}
