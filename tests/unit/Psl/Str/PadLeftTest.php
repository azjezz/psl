<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class PadLeftTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testPadLeft(string $expected, string $str, int $total_length, string $pad_string = ' '): void
    {
        static::assertSame($expected, Str\pad_left($str, $total_length, $pad_string));
    }

    public function provideData(): array
    {
        return [
            [' aaay', 'aaay', 5],
            ['Aaaay', 'aaay', 5, 'A'],
            ['Yeet', 'eet', 4, 'Yeeeee'],
            ['ممممرحبا', 'مرحبا', 8, 'م'],
        ];
    }
}
