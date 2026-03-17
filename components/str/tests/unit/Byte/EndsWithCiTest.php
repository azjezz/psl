<?php

declare(strict_types=1);

namespace Psl\Str\Tests\Unit\Byte;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class EndsWithCiTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testEndsWithCi(bool $expected, string $haystack, string $suffix): void
    {
        static::assertSame($expected, Byte\ends_with_ci($haystack, $suffix));
    }

    public static function provideData(): array
    {
        return [
            [true,  'Hello',         'Hello'],
            [false, 'Hello, WorlḐ',  'worlḑ'],
            [true,  'Hello, Worlḑ',  'worlḑ'],
            [false, 'T U N I S I A', 'e'],
            [true,  'تونس',          'س'],
            [false, 'Hello, World',  ''],
            [false, 'hello, world',  'hey'],
            [false, 'hello, worlḑ',  'hello cruel worḑ'],
            [true,  'azjezz',        'z'],
            [true,  'مرحبا بكم',     'بكم'],
        ];
    }
}
