<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str\Grapheme;

use PHPUnit\Framework\TestCase;
use Psl\Str\Grapheme;

final class StripSuffixTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testStripSuffix(string $expected, string $haystack, string $suffix): void
    {
        static::assertSame($expected, Grapheme\strip_suffix($haystack, $suffix));
    }

    public function provideData(): array
    {
        return [
            ['', 'Hello', 'Hello'],
            ['Hello, World', 'Hello, World', 'world'],
            ['T U N I S I A', 'T U N I S I A', 'e'],
            ['تون', 'تونس', 'س'],
            ['Hello, World', 'Hello, World', ''],
            ['Hello, World', 'Hello, World', 'Hello, cruel world!'],
            ['hello, world', 'hello, world', 'hey'],
            ['azjez', 'azjezz', 'z'],
            ['مرحبا ', 'مرحبا بكم', 'بكم'],
            ['Hello', 'Hello, World', ', World'],
            ['Hello, World', 'Hello, World', 'world'],
            ['Hello, World', 'Hello, World', ''],
            ['hello, world', 'hello, world', 'universe'],
            ['azje', 'azjezz', 'zz'],
            ['azjezz', 'azjezz', 'ZZ'],
            ['مرحبا', 'مرحبا سيف', ' سيف', 3],
            ['اهلا', 'اهلا بكم', ' بكم', 3],
            ['héllö', 'héllö wôrld', ' wôrld'],
            ['héllö wôrld', 'héllö wôrld', ' world'],
            ['fô', 'fôo', 'o'],
            ['fôo', 'fôo', 'ô'],
            ['f', 'fôo', 'ôo'],
        ];
    }
}
