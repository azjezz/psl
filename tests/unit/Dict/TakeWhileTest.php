<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use PHPUnit\Framework\TestCase;
use Psl\Dict;

final class TakeWhileTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testTakeWhile(array $expected, array $array, callable $callable): void
    {
        $result = Dict\take_while($array, $callable);

        static::assertSame($expected, $result);
    }

    public function provideData(): iterable
    {
        yield [[], [1, 2, 3, 4, 5], static fn(int $_): bool => false];
        yield [[1, 2, 3], [1, 2, 3, 4, 5], static fn(int $i) => $i <= 3];
        yield [[1, 2], [1, 2, 3, 4, 5], static fn(int $i) => $i <= 2];
        yield [[1, 2, 3, 4, 5], [1, 2, 3, 4, 5], static fn(int $_) => true];
    }
}
