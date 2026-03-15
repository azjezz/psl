<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str;

final class PadRightTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testPadRight(string $expected, string $str, int $totalLength, string $padString = ' '): void
    {
        static::assertSame($expected, Str\pad_right($str, $totalLength, $padString));
    }

    public static function provideData(): array
    {
        return [
            ['aaay ',    'aaay',  5],
            ['aaayy',    'aaay',  5, 'y'],
            ['Yeet',     'Yee',   4, 't'],
            ['مرحباااا', 'مرحبا', 8, 'ا'],
        ];
    }
}
