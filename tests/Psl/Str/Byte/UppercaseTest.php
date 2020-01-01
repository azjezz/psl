<?php

declare(strict_types=1);

namespace Psl\Tests\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

class UppercaseTest extends TestCase
{

    /**
     * @dataProvider provideData
     */
    public function testUppercase(string $expected, string $str): void
    {
        self::assertSame($expected, Byte\uppercase($str));
    }

    public function provideData(): array
    {
        return [
            ['HELLO', 'hello'],
            ['HELLO', 'helLO'],
            ['HELLO', 'Hello'],
            ['HéLLö, WôRLD!', 'héllö, wôrld!'],
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
