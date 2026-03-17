<?php

declare(strict_types=1);

namespace Psl\Str\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str;

final class TrimRightTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testTrimRight(string $expected, string $string, null|string $chars = null): void
    {
        static::assertSame($expected, Str\trim_right($string, $chars));
    }

    public static function provideData(): array
    {
        return [
            ["    Hello     World\t!!!",     "    Hello     World\t!!!\n",   null],
            ["    Hello     World\t!!!\n",   "    Hello     World\t!!!\n",   ' '],
            ["    Hello     World\t!!!",     "    Hello     World\t!!!\n",   "\n"],
            ["    Hello     World\t",        "    Hello     World\t!!!\n",   "\n!"],
            ['    Hello     World',          "    Hello     World\t!!!\n",   "\n!\t"],
            ["    Hello     World\t",        "    Hello     World\t!!!\n",   " \n!"],
            ["    Hello     World\t!!!  \n", "    Hello     World\t!!!  \n", ' '],
        ];
    }

    #[DataProvider('provideBadUtf8Data')]
    public function testBadUtf8(string $string, string $expectedException, string $expectedExceptionMessage): void
    {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        Str\trim_right($string);
    }

    public static function provideBadUtf8Data(): iterable
    {
        yield [
            "\xc1\xbf",
            Str\Exception\InvalidArgumentException::class,
            'Malformed UTF-8 characters, possibly incorrectly encoded',
        ];

        yield [
            "\xe0\x81\xbf",
            Str\Exception\InvalidArgumentException::class,
            'Malformed UTF-8 characters, possibly incorrectly encoded',
        ];

        yield [
            "\xf0\x80\x81\xbf",
            Str\Exception\InvalidArgumentException::class,
            'Malformed UTF-8 characters, possibly incorrectly encoded',
        ];
    }
}
