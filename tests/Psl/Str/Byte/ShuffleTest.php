<?php

declare(strict_types=1);

namespace Psl\Tests\Str\Byte;

use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

class ShuffleTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testShuffle(string $str): void
    {
        $shuffled = Byte\shuffle($str);
        self::assertSame(Byte\length($str), Byte\length($shuffled));

        if (Byte\length($shuffled) > 1) {
            self::assertNotSame($str, $shuffled);
        }
    }

    public function provideData(): array
    {
        return [
            [''],
            ['A'],
            ['Hello, World!'],
            ['People linked by destiny will always find each other.'],
        ];
    }
}
