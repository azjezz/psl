<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class LowercaseTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testLowercase(string $expected, string $str): void
    {
        static::assertSame($expected, Str\lowercase($str));
    }

    public function provideData(): array
    {
        return [
            ['hello', 'hello'],
            ['hello', 'Hello'],
            ['سيف', 'سيف'],
            ['1337', '1337'],
            ['héllö, wôrld!', 'HÉLLÖ, WÔRLD!'],
            ['héllö, wôrld!', 'héllö, wôrld!'],
            ['ß', 'ß'],
            ['ß', 'ẞ'],
            ['🤷 🔥', '🤷 🔥'],
            ['سيف', 'سيف'],
            ['1337', '1337'],
            ['你好', '你好'],
            ['こんにちは世界', 'こんにちは世界'],
            ['สวัสดี', 'สวัสดี'],
            ['ؤخى', 'ؤخى'],
        ];
    }
}
