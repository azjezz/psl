<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class BeforeCiTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testAfter(
        ?string $expected,
        string $haystack,
        string $needle,
        int $offset,
        ?string $encoding
    ): void {
        static::assertSame($expected, Str\before_ci($haystack, $needle, $offset, $encoding));
    }

    public function provideData(): array
    {
        return [
            [null, '', '',  0, null],
            ['Hello, ', 'Hello, World!', 'W', 0, null],
            ['', '🤷!', '🤷', 0, null],
            [null, 'مرحبا بكم', '', 0, null],
            [null, 'مرحبا بكم', 'ß', 0, null],
            ['', 'héllö, wôrld!', 'héllö', 0, null],
            ['', 'ḫéllö, wôrld!', 'ḫéllö', 0, null],
            ['', 'ḫéllö, wôrld!', 'Ḫéllö', 0, null],
            ['', 'Ḫéllö, wôrld!', 'Ḫéllö', 0, null],
            ['', 'Ḫéllö, wôrld!', 'ḫéllö', 0, null],
            ['héllö, ', 'héllö, wôrld!', 'w', 0, null],
            ['ḫéllö, ', 'ḫéllö, wôrld!', 'w', 0, null],
            ['ḫéllö, ', 'ḫéllö, wôrld!', 'w', 0, null],
            ['Ḫéllö, ', 'Ḫéllö, Wôrld!', 'w', 0, null],
            ['Ḫéllö, ', 'Ḫéllö, wôrld!', 'w', 0, null],
            ['你', '你好', '好', 0, null],
            ['', '你好', '你', 0, null],
            ['こんにちは世', 'こんにちは世界', '界', 0, null],
            ['こんに', 'こんにちは世界', 'ち', 0, null],
            ['ส ว ั ส ด', 'ส ว ั ส ด ี', ' ี', 0, null],
            ['Hell', 'Hello, world!', 'o', 0, null],
            ['Hello, w', 'Hello, world!', 'o', 7, null],
        ];
    }
}
