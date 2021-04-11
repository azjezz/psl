<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class MapWithKeyTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMapWithKey(array $expected, iterable $iterable, callable $function): void
    {
        $result = Iter\map_with_key($iterable, $function);

        static::assertSame($expected, Iter\to_array_with_keys($result));
    }

    public function provideData(): iterable
    {
        yield [[1, 3, 5], [1, 2, 3], static fn (int $k, int $v): int => $k + $v];
        yield [[0, 4, 16], [1, 2, 3], static fn (int $k, int $v): int => $k * (2 ** $v)];
        yield [['1', '3', '5'], [1, 2, 3], static fn (int $k, int $v): string => (string) ($k + $v)];
        yield [[], [], static fn (int $k, int $v): string => (string) ($k + $v)];
        yield [[], Iter\Iterator::create([]), static fn (int $k, int $v): string => (string) ($k + $v)];
        yield [['1', '3'], Iter\Iterator::create([1, 2]), static fn (int $k, int $v): string => (string) ($k + $v)];
    }
}
