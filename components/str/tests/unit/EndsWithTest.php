<?php

declare(strict_types=1);

namespace Psl\Str\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str;

final class EndsWithTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testEndsWith(bool $expected, string $haystack, string $suffix): void
    {
        static::assertSame($expected, Str\ends_with($haystack, $suffix));
    }

    public static function provideData(): array
    {
        return [
            [true,  'Hello',         'Hello'],
            [false, 'Hello, World',  'world'],
            [false, 'T U N I S I A', 'e'],
            [true,  'تونس',          'س'],
            [false, 'Hello, World',  ''],
            [false, 'Hello, World',  'Hello, cruel world!'],
            [false, 'hello, world',  'hey'],
            [true,  'azjezz',        'z'],
            [true,  'مرحبا بكم',     'بكم'],
        ];
    }

    public function testEndsWithNonUtf8Encoding(): void
    {
        static::assertTrue(Str\ends_with('Hello, World', 'World', Str\Encoding::Iso88591));
        static::assertFalse(Str\ends_with('Hello, World', 'world', Str\Encoding::Iso88591));
        static::assertTrue(Str\ends_with('Hello', 'Hello', Str\Encoding::Iso88591));
        static::assertFalse(Str\ends_with('Hi', 'Hello, World', Str\Encoding::Iso88591));
    }
}
