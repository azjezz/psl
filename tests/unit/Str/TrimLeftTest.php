<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Str;

final class TrimLeftTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testTrimLeft(string $expected, string $string, ?string $chars = null): void
    {
        static::assertSame($expected, Str\trim_left($string, $chars));
    }

    public function provideData(): array
    {
        return [
            [
                "Hello     Wôrld\t!!!\n",
                "    Hello     Wôrld\t!!!\n",
                null,
            ],
            [
                "Hello     Wôrld\t!!!\n",
                "    Hello     Wôrld\t!!!\n",
                ' ',
            ],
            [
                "    Héllö     World\t!!!\n",
                "    Héllö     World\t!!!\n",
                "\n",
            ],
            [
                "Héllö     World\t!!!\n",
                "    Héllö     World\t!!!\n",
                " \n!",
            ],
            [
                "Héllö     Wôrld\t!!!  \n",
                "    Héllö     Wôrld\t!!!  \n",
                ' ',
            ],
        ];
    }

    /**
     * @dataProvider provideBadUtf8Data
     */
    public function testBadUtf8(string $string, string $expectedException, string $expectedExceptionMessage): void
    {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        Str\trim_left($string);
    }

    public function provideBadUtf8Data(): iterable
    {
        yield [
            "\xc1\xbf",
            Psl\Exception\InvariantViolationException::class,
            'Expected $string to be a valid UTF-8 string.',
        ];

        yield [
            "\xe0\x81\xbf",
            Psl\Exception\InvariantViolationException::class,
            'Expected $string to be a valid UTF-8 string.',
        ];

        yield [
            "\xf0\x80\x81\xbf",
            Psl\Exception\InvariantViolationException::class,
            'Expected $string to be a valid UTF-8 string.',
        ];
    }
}
