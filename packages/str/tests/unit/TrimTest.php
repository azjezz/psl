<?php

declare(strict_types=1);

namespace Psl\Str\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str;

final class TrimTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testTrim(string $expected, string $string, null|string $chars = null): void
    {
        static::assertSame($expected, Str\trim($string, $chars));
    }

    public static function provideData(): array
    {
        return [
            ["Hello     Wôrld\t!!!",     "    Hello     Wôrld\t!!!\n",   null],
            ["Hello     Wôrld\t!!!\n",   "    Hello     Wôrld\t!!!\n",   ' '],
            ["    Héllö     World\t!!!", "    Héllö     World\t!!!\n",   "\n"],
            ["Héllö     World\t",        "    Héllö     World\t!!!\n",   " \n!"],
            ['Héllö     World',          "    Héllö     World\t!!!\n",   " \n!\t"],
            ["Héllö     Wôrld\t!!!  \n", "    Héllö     Wôrld\t!!!  \n", ' '],
        ];
    }

    #[DataProvider('provideBadUtf8Data')]
    public function testBadUtf8(string $string, string $expectedException, string $expectedExceptionMessage): void
    {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        Str\trim($string);
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
