<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

class EncodingTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testDetectEncoding(?string $expected, string $string): void
    {
        self::assertSame($expected, Str\detect_encoding($string));
    }

    public function provideData(): array
    {
        return [
            ['ASCII', 'hello'],
            ['UTF-8', 'سيف'],
            ['UTF-8', '🐘'],
            [null, Str\Byte\chr(128)]
        ];
    }
}
