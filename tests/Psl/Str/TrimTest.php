<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class TrimTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testTrim(string $expected, string $string, ?string $chars = null): void
    {
        static::assertSame($expected, Str\trim($string, $chars));
    }

    public function provideData(): array
    {
        return [
            [
                "Hello     Wôrld\t!!!",
                "    Hello     Wôrld\t!!!\n",
                null,
            ],
            [
                "Hello     Wôrld\t!!!\n",
                "    Hello     Wôrld\t!!!\n",
                ' ',
            ],
            [
                "    Héllö     World\t!!!",
                "    Héllö     World\t!!!\n",
                "\n",
            ],
            [
                "Héllö     World\t",
                "    Héllö     World\t!!!\n",
                " \n!",
            ],
            [
                "Héllö     World",
                "    Héllö     World\t!!!\n",
                " \n!\t",
            ],
            [
                "Héllö     Wôrld\t!!!  \n",
                "    Héllö     Wôrld\t!!!  \n",
                ' ',
            ],
        ];
    }
}
