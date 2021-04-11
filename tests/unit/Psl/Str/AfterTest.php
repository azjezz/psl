<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class AfterTest extends TestCase
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
        static::assertSame($expected, Str\after($haystack, $needle, $offset, $encoding));
    }


    public function provideData(): array
    {
        return [
            [null, '', '',  0, null],
            ['orld!', 'Hello, World!', 'W', 0, null],
            ['!', '🤷!', '🤷', 0, null],
            [null, 'مرحبا بكم', '', 0, null],
            [null, 'مرحبا بكم', 'ß', 0, null],
            [', wôrld!', 'héllö, wôrld!', 'héllö', 0, null],
            [', wôrld!', 'ḫéllö, wôrld!', 'ḫéllö', 0, null],
            [null, 'ḫéllö, wôrld!', 'Ḫéllö', 0, null],
            [', wôrld!', 'Ḫéllö, wôrld!', 'Ḫéllö', 0, null],
            [null, 'Ḫéllö, wôrld!', 'ḫéllö', 0, null],
            ['好', '你好', '你', 0, null],
            ['にちは世界', 'こんにちは世界', 'こん', 0, null],
            ['สดี', 'สวัสดี', 'วั', 0, null],
            [', world!', 'Hello, world!', 'o', 0, null],
            ['rld!', 'Hello, world!', 'o', 7, null],
        ];
    }
}
