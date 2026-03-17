<?php

declare(strict_types=1);

namespace Psl\Str\Tests\Unit\Byte;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class StripPrefixTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testStripPrefix(string $expected, string $haystack, string $prefix): void
    {
        static::assertSame($expected, Byte\strip_prefix($haystack, $prefix));
    }

    public static function provideData(): array
    {
        return [
            ['World',        'Hello, World', 'Hello, '],
            ['Hello, World', 'Hello, World', 'world'],
            ['Hello, World', 'Hello, World', ''],
            ['hello, world', 'hello, world', 'hey'],
            ['jezz',         'azjezz',       'az'],
            ['azjezz',       'azjezz',       'Az'],
            ['مرحبا بكم',    'مرحبا بكم',    'بكم'],
            ['بكم',          'مرحبا بكم',    'مرحبا '],
            ['سيف',          'مرحبا سيف',    'مرحبا '],
            ['مرحبا سيف',    'مرحبا سيف',    'سيف'],
            [' بكم',         'اهلا بكم',      'اهلا'],
            ['wôrld',        'héllö wôrld',  'héllö '],
            ['héllö wôrld',  'héllö wôrld',  'hello'],
            ['',             'fôo',          'fôo'],
            ['ôo',           'fôo',          'f'],
            ['o',            'fôo',          'fô'],
        ];
    }
}
