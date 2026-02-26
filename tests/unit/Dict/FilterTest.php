<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Dict;

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
}
