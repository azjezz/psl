<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class WidthTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testWidth(int $expected, string $str): void
    {
        static::assertSame($expected, Str\width($str));
    }

    public function provideData(): array
    {
        return [
            [5, 'Hello'],
            [8, '☕ ☕ ☕'],
            [1, '⏟'],
            [5, '⸺⸺⸺⸺⸺'],
            [24, '♈♉♊♋♌♍♎♏♐♑♒♓'],
            [1, '༇'],
            [12, 'héllö, wôrld'],
            [9, 'مرحبا بكم'],
            [9, 'مرحبا سيف'],
            [6, 'azjezz'],
            [4, 'تونس'],
            [3, 'سيف'],
            [14, 'こんにちは世界'],
            [6, '🥇🥈🥉'],
            [4, '你好'],
            [6, 'สวัสดี'],
            [3, 'ؤخى'],
        ];
    }
}
