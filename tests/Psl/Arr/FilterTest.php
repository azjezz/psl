<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;

class FilterTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testFilter(array $expected, array $array, ?callable $predicate = null): void
    {
        $result = Arr\filter($array, $predicate);

        self::assertSame($expected, $result);
    }

    public function provideData(): iterable
    {
        yield  [[], []];
        yield  [['a', 'b'], ['a', 'b']];
        yield  [[], ['a', 'b'], fn () => false];
        yield  [['a', 'b'], ['a', 'b'], fn (string $_): bool => true];
        yield  [['a'], ['a', 'b'], fn (string $v): bool => 'b' !== $v];
    }
}
