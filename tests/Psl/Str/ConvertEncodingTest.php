<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

class ConvertEncodingTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testConvertEncoding(?string $expected, string $string, string $from_encoding, string $to_encoding): string
    {
        self::assertSame($expected, Str\convert_encoding($string, $from_encoding, $to_encoding));
    }

    public function provideData(): array
    {
        return [
            ['Ã¥Ã¤Ã¶', 'åäö', 'ISO-8859-1', 'utf-8'],
            ['$to_encoding is invalid.', 'åäö', 'ISO-8859-1', 'uft-8'],
            ['$from_encoding is invalid.', 'åäö', 'uft-8', 'ISO-8859-1']
        ];
    }
}
