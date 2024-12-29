<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class SplitTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSplit(array $expected, string $string, string $delimiter, null|int $length = null): void
    {
        static::assertSame($expected, Byte\split($string, $delimiter, $length));
    }

    public function provideData(): array
    {
        return [
            [[], '', '', 1],
            [['H', 'e', 'l', 'l', 'o'], 'Hello', ''],
            [['Hello'], 'Hello', '', 1],
            [['H', 'ello'], 'Hello', '', 2],
            [['Hello, World!'], 'Hello, World!', ' ', 1],
            [['Hello,', 'World!'], 'Hello, World!', ' '],
            [['Hello,', 'World!'], 'Hello, World!', ' ', 2],
        ];
    }
}
