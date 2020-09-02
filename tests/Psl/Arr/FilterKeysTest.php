<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;

class FilterKeysTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFilterKeys(array $expected, array $array, ?callable $predicate = null): void
    {
        $result = Arr\filter_keys($array, $predicate);

        self::assertSame($expected, $result);
    }

    public function provideData(): iterable
    {
        yield  [[], []];
        yield  [[1 => 'b'], ['a', 'b']];
        yield  [[], ['a', 'b'], fn () => false];
        yield  [['a', 'b'], ['a', 'b'], fn (int $_): bool => true];
        yield  [['a'], ['a', 'b'], fn (int $k): bool => 1 !== $k];
    }
}
