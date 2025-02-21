<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;
use Psl\Vec;

final class CountTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testCount(int $expected, iterable $iterable): void
    {
        static::assertSame($expected, Iter\count($iterable));
    }

    public function provideData(): iterable
    {
        yield [0, []];
        yield [1, [null]];
        yield [3, [1, 2, 3]];
        yield [10, Vec\range(1, 10)];
        yield [1, (static fn(): iterable => yield 1 => 2)()];
        yield [21, Collection\Vector::fromArray(Vec\range(0, 100, 5))];
    }
}
