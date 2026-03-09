<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Dict;

final class EqualTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testEqualReturnsTheExpectedValue(bool $expected, array $array, array $other): void
    {
        static::assertSame($expected, Dict\equal($array, $other));
    }

    public static function provideData(): array
    {
        return [
            [
                true,
                ['foo' => 'bar', 'baz' => 'qux'],
                ['baz' => 'qux', 'foo' => 'bar'],
            ],
            [
                false,
                ['foo' => 0, 'baz' => 1],
                ['foo' => '0', 'baz' => 1],
            ],
            [
                true,
                [],
                [],
            ],
            [
                false,
                [null],
                [],
            ],
            [
                false,
                [new Collection\Vector([])],
                [new Collection\Vector([])],
            ],
        ];
    }
}
