<?php

declare(strict_types=1);

namespace Psl\Str\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str;

final class ConvertEncodingTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testConvertEncoding(
        null|string $expected,
        string $string,
        Str\Encoding $fromEncoding,
        Str\Encoding $toEncoding,
    ): void {
        static::assertSame($expected, Str\convert_encoding($string, $fromEncoding, $toEncoding));
    }

    public static function provideData(): array
    {
        return [['Ã¥Ã¤Ã¶', 'åäö', Str\Encoding::Iso88591, Str\Encoding::Utf8]];
    }
}
