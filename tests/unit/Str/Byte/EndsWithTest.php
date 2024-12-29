<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class EndsWithTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testEndsWith(bool $expected, string $haystack, string $suffix): void
    {
        static::assertSame($expected, Byte\ends_with($haystack, $suffix));
    }

    public function provideData(): array
    {
        return [
            [true, 'Hello', 'Hello'],
            [false, 'Hello, WorlḐ', 'worlḑ'],
            [false, 'Hello, Worlḑ', 'worlḑ'],
            [false, 'T U N I S I A', 'e'],
            [true, 'تونس', 'س'],
            [false, 'Hello, World', ''],
            [false, 'hello, world', 'hey'],
            [false, 'hello, worlḑ', 'hello cruel worḑ'],
            [true, 'azjezz', 'z'],
            [true, 'مرحبا بكم', 'بكم'],
        ];
    }
}
