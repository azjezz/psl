<?php

declare(strict_types=1);

namespace Psl\Str\Tests\Unit\Byte;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class CapitalizeWordsTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testCapitalizeWords(string $expected, string $value): void
    {
        static::assertSame($expected, Byte\capitalize_words($value));
    }

    public static function provideData(): array
    {
        return [
            ['Hello',             'hello'],
            ['Hello, World',      'hello, world'],
            ['Alpha',             'Alpha'],
            ['Foo, Bar, And Baz', 'foo, bar, and baz'],
        ];
    }
}
