<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

class TrimRightTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testTrimRight(string $expected, string $string, ?string $chars = null): void
    {
        self::assertSame($expected, Str\trim_right($string, $chars));
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
}
