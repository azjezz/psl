<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;

/**
 * @covers \Psl\Arr\shuffle
 */
class ShuffleTest extends TestCase
{
    public function testShuffle(): void
    {
        $array = [
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'qux',
        ];

        $shuffled = Arr\shuffle($array);

        static::assertSameSize($shuffled, $array);
        static::assertNotSame($shuffled, $array);

        foreach ($shuffled as $value) {
            self::assertTrue(Arr\contains($array, $value));
        }
    }
}
