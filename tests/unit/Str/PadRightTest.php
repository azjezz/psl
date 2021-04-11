<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class PadRightTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testPadRight(string $expected, string $str, int $total_length, string $pad_string = ' '): void
    {
        static::assertSame($expected, Str\pad_right($str, $total_length, $pad_string));
    }

    public function provideData(): array
    {
        return [
            ['aaay ', 'aaay', 5],
            ['aaayy', 'aaay', 5, 'y'],
            ['Yeet', 'Yee', 4, 't'],
            ['مرحباااا', 'مرحبا', 8, 'ا'],
        ];
    }
}
