<?php

declare(strict_types=1);

namespace Psl\Tests\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

class PadRightTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testPadRight(string $expected, string $str, int $total_length, string $pad_string = ' '): void
    {
        self::assertSame($expected, Byte\pad_right($str, $total_length, $pad_string));
    }

    public function provideData(): array
    {
        return [
            ['aaay ', 'aaay', 5],
            ['aaayy', 'aaay', 5, 'y'],
            ['Yeet', 'Yee', 4, 't'],
            ['مرحبا', 'مرحبا', 8, 'ا'],
        ];
    }
}
