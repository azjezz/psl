<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Str;

final class TrimRightTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testTrimRight(string $expected, string $string, ?string $chars = null): void
    {
        static::assertSame($expected, Str\trim_right($string, $chars));
    }

    public function provideData(): array
    {
        return [
            [
                "    Hello     World\t!!!",
                "    Hello     World\t!!!\n",
                null,
            ],
            [
                "    Hello     World\t!!!\n",
                "    Hello     World\t!!!\n",
                ' ',
            ],
            [
                "    Hello     World\t!!!",
                "    Hello     World\t!!!\n",
                "\n",
            ],
            [
                "    Hello     World\t",
                "    Hello     World\t!!!\n",
                "\n!",
            ],
            [
                '    Hello     World',
                "    Hello     World\t!!!\n",
                "\n!\t",
            ],
            [
                "    Hello     World\t",
                "    Hello     World\t!!!\n",
                " \n!",
            ],
            [
                "    Hello     World\t!!!  \n",
                "    Hello     World\t!!!  \n",
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

        Str\trim_right($string);
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
