<?php

declare(strict_types=1);

namespace Psl\Iter\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;
use Psl\Vec;

final class CountTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testCount(int $expected, iterable $iterable): void
    {
        static::assertSame($expected, Iter\count::<int|null>($iterable));
    }

    public static function provideData(): iterable
    {
        yield [0, []];
        yield [1, [null]];
        yield [3, [1, 2, 3]];
        yield [10, Vec\range::<int>(1, 10)];
        yield [1, (static fn(): iterable => yield 1 => 2)()];
        yield [21, Collection\Vector::<int>::fromArray(Vec\range::<int>(0, 100, 5))];
    }
}
