<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;

final class ConcatTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testConcat(array $expected, array $first, array ...$other): void
    {
        static::assertSame($expected, Arr\concat($first, ...$other));
    }

    public function provideData(): array
    {
        return [
            [
                ['a', 'b', 'c'],
                ['foo' => 'a'],
                ['bar' => 'b'],
                ['baz' => 'c'],
            ],
            [
                ['foo', 'bar', 'baz', 'qux'],
                ['foo'],
                ['bar'],
                ['baz', 'qux'],
            ],
            [
                [1, 2, 3],
                [1, 2, 3],
            ],
        ];
    }
}
