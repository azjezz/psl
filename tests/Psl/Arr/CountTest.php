<?php

declare(strict_types=1);

namespace Psl\Tests\Arr;

use PHPUnit\Framework\TestCase;
use Psl\Arr;

final class CountTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testCount(int $expected, array $array): void
    {
        static::assertSame($expected, Arr\count($array));
    }

    public function provideData(): iterable
    {
        yield [0, []];
        yield [2, [1, 2]];
        yield [1, [null]];
        yield [4, [null, null, false, 0]];
    }
}
