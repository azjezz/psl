<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class MapTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMap(array $expected, iterable $iterable, callable $function): void
    {
        $result = Iter\map($iterable, $function);
        
        static::assertSame($expected, Iter\to_array_with_keys($result));
    }

    public function provideData(): iterable
    {
        yield [[1, 2, 3], [1, 2, 3], static fn (int $v): int => $v];
        yield [[2, 4, 6], [1, 2, 3], static fn (int $v): int => $v * 2];
        yield [['1', '2', '3'], [1, 2, 3], static fn (int $v): string => (string) $v];
        yield [[], [], static fn (int $k): string => (string) $v];
        yield [[], Iter\Iterator::create([]), static fn (int $v): string => (string) $v];
        yield [['1', '2'], Iter\Iterator::create([1, 2]), static fn (int $v): string => (string) $v];
    }
}
