<?php

declare(strict_types=1);

namespace Psl\Tests\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

class LowercaseTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testLowercase(string $expected, string $str): void
    {
        self::assertSame($expected, Byte\lowercase($str));
    }

    public function provideData(): array
    {
        return [
            ['hello', 'hello'],
            ['hello', 'Hello'],
            ['سيف', 'سيف'],
            ['1337', '1337'],
            ['hÉllÖ, wÔrld!', 'HÉLLÖ, WÔRLD!'],
            ['héllö, wôrld!', 'héllö, wôrld!'],
            ['ß', 'ß'],
            ['ẞ', 'ẞ'],
            ['🤷 🔥', '🤷 🔥'],
            ['سيف', 'سيف'],
            ['1337', '1337'],
            ['你好', '你好'],
            ['こんにちは世界', 'こんにちは世界'],
            ['สวัสดี', 'สวัสดี'],
            ['ؤخى', 'ؤخى']
        ];
    }
}
