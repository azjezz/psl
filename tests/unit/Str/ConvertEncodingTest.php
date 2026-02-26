<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str;

final class ConvertEncodingTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testConvertEncoding(
        null|string $expected,
        string $string,
        Str\Encoding $from_encoding,
        Str\Encoding $to_encoding,
    ): void {
        static::assertSame($expected, Str\convert_encoding($string, $from_encoding, $to_encoding));
    }

    public static function provideData(): array
    {
        return [['Ã¥Ã¤Ã¶', 'åäö', Str\Encoding::Iso88591, Str\Encoding::Utf8]];
    }
}
