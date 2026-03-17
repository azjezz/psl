<?php

declare(strict_types=1);

namespace Psl\Dict\Tests\Unit;

use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Dict;

final class FilterKeysTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testFilterKeys(array $expected, iterable $iterable, null|Closure $predicate = null): void
    {
        $result = Dict\filter_keys($iterable, $predicate);

        static::assertSame($expected, $result);
    }

    public static function provideData(): iterable
    {
        yield [[], []];
        yield [[1 => 'b'], ['a', 'b']];
        yield [[], ['a', 'b'], static fn(): bool => false];
        yield [['a', 'b'], ['a', 'b'], static fn(int $_): bool => true];
        yield [['a'], ['a', 'b'], static fn(int $k): bool => 1 !== $k];
        yield [['a'], Collection\Vector::fromArray(['a', 'b']), static fn(int $k): bool => 1 !== $k];
    }
}
