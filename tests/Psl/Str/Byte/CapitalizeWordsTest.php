<?php

declare(strict_types=1);

namespace Psl\Tests\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

class CapitalizeWordsTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testCapitalizeWords(string $expected, string $value): void
    {
        self::assertSame($expected, Byte\capitalize_words($value));
    }

    public function provideData(): array
    {
        return [
            ['Hello', 'hello', ],
            ['Hello, World', 'hello, world'],
            ['ḫello, ꝡorld', 'ḫello, ꝡorld'],
            ['Alpha', 'Alpha', ],
            ['Foo, Bar, And Baz', 'foo, bar, and baz']
        ];
    }
}
