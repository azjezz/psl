<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

class WidthTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testWidth(int $expected, string $str): void
    {
        self::assertSame($expected, Str\width($str));
    }

    public function provideData(): array
    {
        return [
            [5, 'Hello'],
            [5, '☕ ☕ ☕'],
            [1, '⏟'],
            [5, '⸺⸺⸺⸺⸺'],
            [12, '♈♉♊♋♌♍♎♏♐♑♒♓'],
            [1, '༇'],
            [12, 'héllö, wôrld'],
            [9, 'مرحبا بكم'],
            [9, 'مرحبا سيف'],
            [6, 'azjezz'],
            [4, 'تونس'],
            [3, 'سيف'],
            [14, 'こんにちは世界'],
            [3, '🥇🥈🥉'],
            [4, '你好'],
            [6, 'สวัสดี'],
            [3, 'ؤخى']
        ];
    }
}
