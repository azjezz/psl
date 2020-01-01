<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

class RepeatTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testRepeat(string $expected, string $string, int $multiplier): void
    {
        self::assertSame($expected, Str\repeat($string, $multiplier));
    }

    public function provideData(): array
    {
        return [
            ['a', 'a', 1],
            ['Go! Go! Go! ', 'Go! ', 3],
            ['مممممممممممم', 'م', 12],
        ];
    }
}
