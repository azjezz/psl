<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

class MetaphoneTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMetaphone(?string $expected, string $str, int $phonemes = 0): void
    {
        self::assertSame($expected, Str\metaphone($str, $phonemes));
    }

    public function provideData(): array
    {
        return [
            ['HL', 'hello'],
            ['HLWRLT', 'Hello, World !!', 10],
            ['', 'سيف'],
            ['', '1337'],
        ];
    }
}
