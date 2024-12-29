<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class WrapTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testWrap(
        string $expected,
        string $str,
        int $width = 75,
        string $break = "\n",
        bool $cut = false,
    ): void {
        static::assertSame($expected, Str\wrap($str, $width, $break, $cut));
    }

    public function provideData(): array
    {
        return [
            ['Hello', 'Hello'],
            ['', ''],
            ['☕ ~ ☕ ~ ☕', '☕ ☕ ☕', 1, ' ~ '],
            ["♈♉\n♊♋\n♌♍\n♎♏\n♐♑\n♒♓", "♈♉♊♋♌♍♎♏♐♑\n♒♓", 2, "\n", true],
            ["héllö,-\nwôrld", 'héllö, wôrld', 8, "-\n", true],
            ['مرحبا<br />بكم', 'مرحبا بكم', 3, '<br />', false],
            ['مرح<br />با<br />بكم', 'مرحبا بكم', 3, '<br />', true],
            ["こんに\nちは世\n界", 'こんにちは世界', 3, "\n", true],
            ['こんにちは世界', 'こんにちは世界', 3, "\n", false],
            ['こんにちは世界', 'こんにちは世界', 7, "\n", true],
            [
                Str\concat('ส', '-', 'ว', '-', 'ั', '-', 'ส', '-', 'ด', '-', 'ี'),
                'สวัสดี',
                1,
                '-',
                true,
            ],
            ['สวัสดี', 'สวัสดี', 1, '-', false],
        ];
    }
}
