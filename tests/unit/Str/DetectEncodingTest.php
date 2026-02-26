<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str;

final class DetectEncodingTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testDetectEncoding(null|Str\Encoding $expected, string $string): void
    {
        static::assertSame($expected, Str\detect_encoding($string));
    }

    public static function provideData(): array
    {
        return [
            [Str\Encoding::Ascii, 'hello'],
            [Str\Encoding::Utf8, 'سيف'],
            [Str\Encoding::Utf8, '🐘'],
            [null, Str\Byte\chr(128)],
        ];
    }
}
