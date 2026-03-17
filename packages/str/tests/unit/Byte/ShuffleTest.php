<?php

declare(strict_types=1);

namespace Psl\Str\Tests\Unit\Byte;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Str\Byte;

final class ShuffleTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testShuffle(string $str): void
    {
        $shuffled = Byte\shuffle($str);
        static::assertSame(Byte\length($str), Byte\length($shuffled));

        if (Byte\length($shuffled) > 1) {
            static::assertNotSame($str, $shuffled);
        }
    }

    public static function provideData(): array
    {
        return [
            [''],
            ['A'],
            ['Hello, World!'],
            ['People linked by destiny will always find each other.'],
        ];
    }
}
