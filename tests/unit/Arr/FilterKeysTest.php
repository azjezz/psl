<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;

final class FilterKeysTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFilterKeys(array $expected, array $array, ?callable $predicate = null): void
    {
        $result = Arr\filter_keys($array, $predicate);

        static::assertSame($expected, $result);
    }

    public function provideData(): iterable
    {
        yield  [[], []];
        yield  [[1 => 'b'], ['a', 'b']];
        yield  [[], ['a', 'b'], static fn () => false];
        yield  [['a', 'b'], ['a', 'b'], static fn (int $_): bool => true];
        yield  [['a'], ['a', 'b'], static fn (int $k): bool => 1 !== $k];
    }
}
