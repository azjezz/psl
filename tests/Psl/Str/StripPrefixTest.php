<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class StripPrefixTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testStripPrefix(string $expected, string $haystack, string $prefix): void
    {
        static::assertSame($expected, Str\strip_prefix($haystack, $prefix));
    }

    public function provideData(): array
    {
        return [
            ['World', 'Hello, World', 'Hello, '],
            ['Hello, World', 'Hello, World', 'world'],
            ['Hello, World', 'Hello, World', ''],
            ['hello, world', 'hello, world', 'hey'],
            ['jezz', 'azjezz', 'az'],
            ['azjezz', 'azjezz', 'Az'],
            ['مرحبا بكم', 'مرحبا بكم', 'بكم'],
            ['بكم', 'مرحبا بكم', 'مرحبا '],
            ['سيف', 'مرحبا سيف', 'مرحبا ', 3],
            ['مرحبا سيف', 'مرحبا سيف', 'سيف', 3],
            [' بكم', 'اهلا بكم', 'اهلا', 2],
            ['wôrld', 'héllö wôrld', 'héllö '],
            ['héllö wôrld', 'héllö wôrld', 'hello'],
            ['', 'fôo', 'fôo'],
            ['ôo', 'fôo', 'f'],
            ['o', 'fôo', 'fô'],
        ];
    }
}
