<?php

declare(strict_types=1);

namespace Psl\Tests\Vec;

use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Vec;

final class ShuffleTest extends TestCase
{
    public function testShuffle(): void
    {
        $array = [
            'foo' => 'bar',
            'bar' => 'baz',
            'baz' => 'qux',
        ];

        $shuffled = Vec\shuffle($array);

        static::assertSameSize($shuffled, $array);
        static::assertNotSame($shuffled, $array);

        foreach ($shuffled as $value) {
            static::assertTrue(Iter\contains($array, $value));
        }
    }
}
