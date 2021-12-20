<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class DetectEncodingTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testDetectEncoding(?Str\Encoding $expected, string $string): void
    {
        static::assertSame($expected, Str\detect_encoding($string));
    }

    public function provideData(): array
    {
        return [
            [Str\Encoding::ASCII, 'hello'],
            [Str\Encoding::UTF_8, 'سيف'],
            [Str\Encoding::UTF_8, '🐘'],
            [null, Str\Byte\chr(128)]
        ];
    }
}
