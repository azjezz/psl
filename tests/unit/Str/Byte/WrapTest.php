<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class WrapTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testWrap(
        string $expected,
        string $str,
        int $width = 75,
        string $break = "\n",
        bool $cut = false,
    ): void {
        static::assertSame($expected, Byte\wrap($str, $width, $break, $cut));
    }

    public function provideData(): array
    {
        return [
            ['Hello', 'Hello'],
            ['', ''],
            ['☕ ~ ☕ ~ ☕', '☕ ☕ ☕', 1, ' ~ '],
            ["héllö,-\nwôrld", 'héllö, wôrld', 8, "-\n", true],
            ['مرحبا<br />بكم', 'مرحبا بكم', 3, '<br />', false],
        ];
    }
}
