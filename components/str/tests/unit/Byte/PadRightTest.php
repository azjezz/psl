<?php

declare(strict_types=1);

namespace Psl\Str\Tests\Unit\Byte;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class PadRightTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testPadRight(string $expected, string $str, int $totalLength, string $padString = ' '): void
    {
        static::assertSame($expected, Byte\pad_right($str, $totalLength, $padString));
    }

    public static function provideData(): array
    {
        return [
            ['aaay ', 'aaay',  5],
            ['aaayy', 'aaay',  5, 'y'],
            ['Yeet',  'Yee',   4, 't'],
            ['مرحبا', 'مرحبا', 8, 'ا'],
        ];
    }
}
