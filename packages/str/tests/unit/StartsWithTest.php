<?php

declare(strict_types=1);

namespace Psl\Str\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str;

final class StartsWithTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testStartsWith(bool $expected, string $haystack, string $prefix): void
    {
        static::assertSame($expected, Str\starts_with($haystack, $prefix));
    }

    public static function provideData(): array
    {
        return [
            [true,  'Hello, World', 'Hello'],
            [false, 'Hello, World', 'world'],
            [false, 'Hello, World', ''],
            [false, 'hello, world', 'hey'],
            [true,  'azjezz',       'az'],
            [false, 'azjezz',       'Az'],
            [false, 'مرحبا بكم',    'بكم'],
            [true,  'مرحبا بكم',    'مرحبا'],
            [true,  'مرحبا سيف',    'مرحبا'],
            [false, 'مرحبا سيف',    'سيف'],
            [true,  'اهلا بكم',      'اهلا'],
            [true,  'héllö wôrld',  'héllö'],
            [false, 'héllö wôrld',  'hello'],
            [true,  'fôo',          'fôo'],
            [true,  'fôo',          'f'],
            [true,  'fôo',          'fô'],
        ];
    }

    public function testStartsWithNonUtf8Encoding(): void
    {
        static::assertTrue(Str\starts_with('Hello, World', 'Hello', Str\Encoding::Iso88591));
        static::assertFalse(Str\starts_with('Hello, World', 'world', Str\Encoding::Iso88591));
    }
}
