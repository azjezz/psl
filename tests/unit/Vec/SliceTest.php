<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Vec;

use PHPUnit\Framework\TestCase;
use Psl\Vec;

final class SliceTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testSlice(array $expected, array $array, int $n, null|int $l = null): void
    {
        $result = Vec\slice($array, $n, $l);

        static::assertSame($expected, $result);
    }

    public function provideData(): iterable
    {
        yield [[0, 1, 2, 3, 4, 5], [-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5];
        yield [[0, 1, 2], [-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5, 3];
    }
}
