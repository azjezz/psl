<?php

declare(strict_types=1);

namespace Psl\Dict\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Dict;

final class SelectKeysTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testSelectKeys(array $result, array $array, array $keys): void
    {
        static::assertSame($result, Dict\select_keys::<string, string>($array, $keys));
    }

    public static function provideData(): array
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
