<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str;

final class ContainsTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testContains(bool $expected, string $haystack, string $needle, int $offset = 0): void
    {
        static::assertSame($expected, Str\contains($haystack, $needle, $offset));
    }

    public static function provideData(): array
    {
        return [
            [true,  'Hello, World', 'Hello', 0],
            [false, 'Hello, World', 'world', 0],
            [true,  'Hello, World', '',      8],
            [false, 'hello, world', 'hey',   5],
            [true,  'azjezz',       'az',    0],
            [false, 'azjezz',       'Az',    2],
            [true,  'مرحبا بكم',    'بكم',   5],
        ];
    }

    public function testContainsWithNonUtf8Encoding(): void
    {
        static::assertTrue(Str\contains('Hello, World', 'World', 0, Str\Encoding::Iso88591));
        static::assertFalse(Str\contains('Hello, World', 'world', 0, Str\Encoding::Iso88591));
    }
}
