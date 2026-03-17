<?php

declare(strict_types=1);

namespace Psl\Str\Tests\Unit\Grapheme;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str\Grapheme;

final class LengthTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testLength(int $expected, string $str): void
    {
        static::assertSame($expected, Grapheme\length($str));
    }

    public static function provideData(): array
    {
        return [
            [6, 'azjezz'],
            [4, 'تونس'],
            [3, 'سيف'],
            [7, 'こんにちは世界'],
            [3, '🥇🥈🥉'],
            [2, '你好'],
            [4, 'สวัสดี'],
            [3, 'ؤخى'],
        ];
    }
}
