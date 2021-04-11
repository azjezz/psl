<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
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
}
