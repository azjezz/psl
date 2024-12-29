<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class CapitalizeTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testCapitalize(string $expected, string $value): void
    {
        static::assertSame($expected, Str\capitalize($value));
    }

    public function provideData(): array
    {
        return [
            ['', ''],
            ['Hello', 'hello'],
            ['Hello, world', 'hello, world'],
            ['Alpha', 'Alpha'],
            ['مرحبا بكم', 'مرحبا بكم'],
            ['Héllö, wôrld!', 'héllö, wôrld!'],
            ['Ḫéllö, wôrld!', 'ḫéllö, wôrld!'],
            ['SSoo', 'ßoo'],
            ['ẞoo', 'ẞoo'],
            ['🤷 🔥', '🤷 🔥'],
            ['سيف', 'سيف'],
            ['你好', '你好'],
            ['こんにちは世界', 'こんにちは世界'],
            ['สวัสดี', 'สวัสดี'],
            ['ؤخى', 'ؤخى'],
        ];
    }
}
