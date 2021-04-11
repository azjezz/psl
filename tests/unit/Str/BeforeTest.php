<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class BeforeTest extends TestCase
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
        static::assertSame($expected, Str\before($haystack, $needle, $offset, $encoding));
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
            [null, 'ḫéllö, wôrld!', 'Ḫéllö', 0, null],
            ['', 'Ḫéllö, wôrld!', 'Ḫéllö', 0, null],
            [null, 'Ḫéllö, wôrld!', 'ḫéllö', 0, null],
            [null, 'héllö, Wôrld!', 'wôrld', 0, null],
            [null, 'ḫéllö, wôrld!', 'Wôrld', 0, null],
            [null, 'ḫéllö, Wôrld!', 'wôrld', 0, null],
            [null, 'Ḫéllö, wôrld!', 'Wôrld', 0, null],
            [null, 'Ḫéllö, Wôrld!', 'wôrld', 0, null],
            ['Ḫéllö, ', 'Ḫéllö, Wôrld!', 'Wôrld', 0, null],
            ['你', '你好', '好', 0, null],
            ['こんにちは', 'こんにちは世界', '世界', 0, null],
            ['สวัส', 'สวัสดี', 'ดี', 0, null],
            ['Hell', 'Hello, world!', 'o', 0, null],
            ['Hello, w', 'Hello, world!', 'o', 7, null],
        ];
    }
}
