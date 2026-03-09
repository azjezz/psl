<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str;

final class StartsWithCiTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testStartsWithCi(bool $expected, string $haystack, string $prefix): void
    {
        static::assertSame($expected, Str\starts_with_ci($haystack, $prefix));
    }

    public static function provideData(): array
    {
        return [
            [true,  'Hello, World', 'Hello'],
            [false, 'Hello, World', 'world'],
            [false, 'Hello, World', ''],
            [false, 'hello, world', 'hey'],
            [true,  'azjezz',       'az'],
            [true,  'azjezz',       'Az'],
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
}
