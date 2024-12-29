<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class EndsWithCiTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testEndsWithCi(bool $expected, string $haystack, string $suffix): void
    {
        if (null === Str\search_ci($haystack, $suffix)) {
            static::assertFalse(Str\ends_with_ci($haystack, $suffix));
        } else {
            static::assertSame($expected, Str\ends_with_ci($haystack, $suffix));
        }
    }

    public function provideData(): array
    {
        return [
            [true, 'Hello', 'Hello'],
            [true, 'Hello, World', 'world'],
            [true, 'Hello, WorlḐ', 'worlḑ'],
            [false, 'T U N I S I A', 'e'],
            [true, 'تونس', 'س'],
            [false, 'Hello, World', ''],
            [false, 'hello, world', 'hey'],
            [false, 'hello, world', 'hello cruel world'],
            [true, 'azjezz', 'z'],
            [true, 'مرحبا بكم', 'بكم'],
        ];
    }
}
