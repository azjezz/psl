<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str\Byte;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class LengthTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testLength(int $expected, string $str): void
    {
        static::assertSame($expected, Byte\length($str));
    }

    public static function provideData(): array
    {
        return [
            [6,  'azjezz'],
            [8,  'تونس'],
            [6,  'سيف'],
            [21, 'こんにちは世界'],
            [12, '🥇🥈🥉'],
            [6,  '你好'],
            [18, 'สวัสดี'],
            [6,  'ؤخى'],
        ];
    }
}
