<?php

declare(strict_types=1);

namespace Psl\Str\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str;

final class ReplaceTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testReplace(string $expected, string $haystack, string $needle, string $replacement): void
    {
        static::assertSame($expected, Str\replace($haystack, $needle, $replacement));
    }

    public static function provideData(): array
    {
        return [
            ['Hello, you!',   'Hello, you!', 'You', 'World'],
            ['Hello, World!', 'Hello, You!', 'You', 'World'],
            ['مرحبا بكم',     'مرحبا سيف',   'سيف', 'بكم'],
            ['foo',           'foo',         'bar', 'baz'],
        ];
    }

    public function testReplaceWithEmptyNeedle(): void
    {
        static::assertSame('Hello', Str\replace('Hello', '', 'World'));
    }

    public function testReplaceWithNonUtf8Encoding(): void
    {
        static::assertSame('Hello, World!', Str\replace('Hello, You!', 'You', 'World', Str\Encoding::Iso88591));
    }

    public function testReplaceWithNonUtf8EncodingNoMatch(): void
    {
        static::assertSame('Hello', Str\replace('Hello', 'xyz', 'World', Str\Encoding::Iso88591));
    }
}
