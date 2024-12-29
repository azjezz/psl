<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class BeforeLastTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testAfter(null|string $expected, string $haystack, string $needle, int $offset): void
    {
        static::assertSame($expected, Str\before_last($haystack, $needle, $offset));
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
            [null, 'héllö, Wôrld!', 'wôrld', 0],
            [null, 'ḫéllö, wôrld!', 'Wôrld', 0],
            [null, 'ḫéllö, Wôrld!', 'wôrld', 0],
            [null, 'Ḫéllö, wôrld!', 'Wôrld', 0],
            [null, 'Ḫéllö, Wôrld!', 'wôrld', 0],
            ['Ḫéllö, ', 'Ḫéllö, Wôrld!', 'Wôrld', 0],
            ['你', '你好', '好', 0],
            ['こんにちは', 'こんにちは世界', '世界', 0],
            ['สวัส', 'สวัสดี', 'ดี', 0],
            ['Hello, w', 'Hello, world!', 'o', 0],
            ['Hello, w', 'Hello, world!', 'o', 7],
        ];
    }
}
