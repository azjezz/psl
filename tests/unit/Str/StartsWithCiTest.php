<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class StartsWithCiTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testStartsWithCi(bool $expected, string $haystack, string $prefix): void
    {
        static::assertSame($expected, Str\starts_with_ci($haystack, $prefix));
    }

    public function provideData(): array
    {
        return [
            [true, 'Hello, World', 'Hello'],
            [false, 'Hello, World', 'world'],
            [false, 'Hello, World', ''],
            [false, 'hello, world', 'hey'],
            [true, 'azjezz', 'az'],
            [true, 'azjezz', 'Az'],
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
