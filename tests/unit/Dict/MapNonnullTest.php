<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Dict;

use Closure;
use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Dict;
use Psl\Iter;

final class MapNonnullTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMapNonnull(array $expected, iterable $iterable, Closure $function): void
    {
        $result = Dict\map_nonnull($iterable, $function);

        static::assertSame($expected, $result);
    }

    public function provideData(): iterable
    {
        yield [[], [], static fn(int $v): null|int => $v];
        yield [[0 => 1, 1 => 2, 2 => 3], [1, 2, 3], static fn(int $v): null|int => $v];
        yield [[1 => 4, 2 => 6], [1, 2, 3], static fn(int $v): null|int => $v > 1 ? $v * 2 : null];
        yield [['a' => 1, 'c' => 3], ['a' => 1, 'b' => null, 'c' => 3], static fn(null|int $v): null|int => $v];
        yield [[], [null, null], static fn(null|string $v): null|string => $v];
        yield [[0 => 0, 1 => 0, 2 => 0], [1, 2, 3], static fn(int $v): int => 0];
        yield [
            [1 => 4, 2 => 6],
            Collection\Vector::fromArray([1, 2, 3]),
            static fn(int $v): null|int => $v > 1 ? $v * 2 : null,
        ];
        yield [
            [1 => 2, 3 => 4],
            Iter\Iterator::create([1, 2, 3, 4]),
            static fn(int $v): null|int => ($v % 2) === 0 ? $v : null,
        ];
    }
}
