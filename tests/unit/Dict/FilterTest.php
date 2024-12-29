<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use PHPUnit\Framework\TestCase;
use Psl\Dict;

final class FilterTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFilter(array $expected, array $array, null|callable $predicate = null): void
    {
        $result = Dict\filter($array, $predicate);

        static::assertSame($expected, $result);
    }

    public function provideData(): iterable
    {
        yield [[], []];
        yield [['a', 'b'], ['a', 'b']];
        yield [[], ['a', 'b'], static fn() => false];
        yield [['a', 'b'], ['a', 'b'], static fn(string $_): bool => true];
        yield [['a'], ['a', 'b'], static fn(string $v): bool => 'b' !== $v];
    }
}
