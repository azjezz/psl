<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str\Grapheme;

use PHPUnit\Framework\TestCase;
use Psl\Str\Grapheme;

final class StartsWithTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testStartsWith(bool $expected, string $haystack, string $prefix): void
    {
        static::assertSame($expected, Grapheme\starts_with($haystack, $prefix));
    }

    public function provideData(): array
    {
        return [
            [true, 'Hello, World', 'Hello'],
            [false, 'Hello, World', 'world'],
            [false, 'Hello, World', ''],
            [false, 'hello, world', 'hey'],
            [true, 'azjezz', 'az'],
            [false, 'azjezz', 'Az'],
            [false, 'مرحبا بكم', 'بكم'],
            [true, 'مرحبا بكم', 'مرحبا'],
            [true, 'مرحبا سيف', 'مرحبا', 3],
            [false, 'مرحبا سيف', 'سيف', 3],
            [true, 'اهلا بكم', 'اهلا', 2],
            [true, 'héllö wôrld', 'héllö'],
            [false, 'héllö wôrld', 'hello'],
            [true, 'fôo', 'fôo'],
            [true, 'fôo', 'f'],
            [true, 'fôo', 'fô'],
        ];
    }
}
