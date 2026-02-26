<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str\Byte;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class PadLeftTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testPadLeft(string $expected, string $str, int $total_length, string $pad_string = ' '): void
    {
        static::assertSame($expected, Byte\pad_left($str, $total_length, $pad_string));
    }

    public static function provideData(): array
    {
        return [
            [' aaay', 'aaay',  5],
            ['Aaaay', 'aaay',  5, 'A'],
            ['Yeet',  'eet',   4, 'Yeeeee'],
            ['مرحبا', 'مرحبا', 8, 'م'],
        ];
    }
}
