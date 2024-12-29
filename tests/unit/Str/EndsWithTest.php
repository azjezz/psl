<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class EndsWithTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testEndsWith(bool $expected, string $haystack, string $suffix): void
    {
        static::assertSame($expected, Str\ends_with($haystack, $suffix));
    }

    public function provideData(): array
    {
        return [
            [true, 'Hello', 'Hello'],
            [false, 'Hello, World', 'world'],
            [false, 'T U N I S I A', 'e'],
            [true, 'تونس', 'س'],
            [false, 'Hello, World', ''],
            [false, 'Hello, World', 'Hello, cruel world!'],
            [false, 'hello, world', 'hey'],
            [true, 'azjezz', 'z'],
            [true, 'مرحبا بكم', 'بكم'],
        ];
    }
}
