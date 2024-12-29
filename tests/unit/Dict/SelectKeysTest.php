<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use PHPUnit\Framework\TestCase;
use Psl\Dict;

final class SelectKeysTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSelectKeys(array $result, array $array, array $keys): void
    {
        static::assertSame($result, Dict\select_keys($array, $keys));
    }

    public function provideData(): array
    {
        return [
            [
                ['foo' => 'bar', 'bar' => 'baz'],
                ['foo' => 'bar', 'bar' => 'baz', 'baz' => 'qux', 'qux' => 'foo'],
                ['foo', 'bar'],
            ],
            [
                [],
                ['baz' => 'qux', 'qux' => 'foo'],
                ['foo', 'bar'],
            ],
            [
                [],
                [],
                ['foo', 'bar'],
            ],
            [
                [],
                ['foo' => 'bar', 'bar' => 'baz', 'baz' => 'qux', 'qux' => 'foo'],
                [],
            ],
            [
                [],
                ['foo' => 'bar', 'bar' => 'baz', 'baz' => 'qux', 'qux' => 'foo'],
                ['a', 'b'],
            ],
        ];
    }
}
