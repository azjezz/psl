<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str\Byte;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class WordsTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testWords(array $expected, string $string, null|string $extra_chars = null): void
    {
        static::assertSame($expected, Byte\words($string, $extra_chars));
    }

    public static function provideData(): array
    {
        return [
            [[], ''],
            [['Hello'], 'Hello'],
            [['Hello'], 'Hello', ' '],
            [[0 => 'Hello', 7 => 'World'], 'Hello, World!'],
            [[0 => 'Hello', 6 => ' World'], 'Hello, World!', ' '],
            [[0 => 'Hello', 7 => 'World!'], 'Hello, World!', '!'],
            [[0 => 'Hello,', 7 => 'World!'], 'Hello, World!', '!,'],
            [[0 => 'Hello, World!'], 'Hello, World!', '!, '],
        ];
    }
}
