<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class UppercaseTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testUppercase(string $expected, string $str): void
    {
        static::assertSame($expected, Str\uppercase($str));
    }

    public function provideData(): array
    {
        return [
            ['HELLO', 'hello'],
            ['HELLO', 'helLO'],
            ['HELLO', 'Hello'],
            ['HÉLLÖ, WÔRLD!', 'héllö, wôrld!'],
            ['SS', 'ß'],
            ['ẞ', 'ẞ'],
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
