<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class CapitalizeWordsTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testCapitalizeWords(string $expected, string $value): void
    {
        static::assertSame($expected, Str\capitalize_words($value));
    }

    public function provideData(): array
    {
        return [
            ['Hello', 'hello'],
            ['Hello, World', 'hello, world'],
            ['Ḫello, Ꝡorld', 'ḫello, ꝡorld'],
            ['Alpha', 'Alpha'],
            ['مرحبا بكم', 'مرحبا بكم'],
            ['Foo, Bar, And Baz', 'foo, bar, and baz'],
        ];
    }
}
