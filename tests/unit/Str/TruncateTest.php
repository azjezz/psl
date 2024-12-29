<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class TruncateTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testTruncate(
        string $expected,
        string $str,
        int $offset,
        int $width,
        null|string $trim_marker = null,
    ): void {
        static::assertSame($expected, Str\truncate($str, $offset, $width, $trim_marker));
    }

    public function provideData(): array
    {
        return [
            ['Hello', 'Hello, World!', 0, 5],
            ['He...', 'Hello, World!', 0, 5, '...'],
            ['Hello...', 'Hello, World!', 0, 8, '...'],
            ['héllö...', 'héllö, wôrld!', 0, 8, '...'],
            ['wôrld!', 'héllö, wôrld!', 7, 8, '...'],
            ['مرحبا...', 'مرحبا بكم', 0, 8, '...'],
            ['سيف', 'مرحبا سيف', 6, 8, '...'],
        ];
    }
}
