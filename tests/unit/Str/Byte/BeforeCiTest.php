<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class BeforeCiTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testAfter(null|string $expected, string $haystack, string $needle, int $offset): void
    {
        static::assertSame($expected, Byte\before_ci($haystack, $needle, $offset));
    }

    public function provideData(): array
    {
        return [
            [null, '', '', 0],
            ['Hello, ', 'Hello, World!', 'W', 0],
            ['', '🤷!', '🤷', 0],
            [null, 'مرحبا بكم', '', 0],
            [null, 'مرحبا بكم', 'ß', 0],
            ['', 'héllö, wôrld!', 'héllö', 0],
            ['', 'ḫéllö, wôrld!', 'ḫéllö', 0],
            [null, 'ḫéllö, wôrld!', 'Ḫéllö', 0],
            ['', 'Ḫéllö, wôrld!', 'Ḫéllö', 0],
            [null, 'Ḫéllö, wôrld!', 'ḫéllö', 0],
            ['héllö, ', 'héllö, wôrld!', 'w', 0],
            ['ḫéllö, ', 'ḫéllö, wôrld!', 'w', 0],
            ['ḫéllö, ', 'ḫéllö, wôrld!', 'w', 0],
            ['Ḫéllö, ', 'Ḫéllö, Wôrld!', 'w', 0],
            ['Ḫéllö, ', 'Ḫéllö, wôrld!', 'w', 0],
            ['你', '你好', '好', 0],
            ['', '你好', '你', 0],
            ['こんにちは世', 'こんにちは世界', '界', 0],
            ['こんに', 'こんにちは世界', 'ち', 0],
            ['ส ว ั ส ด', 'ส ว ั ส ด ี', ' ี', 0],
            ['Hell', 'Hello, world!', 'o', 0],
            ['Hello, w', 'Hello, world!', 'o', 7],
        ];
    }
}
