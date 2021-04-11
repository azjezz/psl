<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;

final class MapKeysTest extends TestCase
{
    /**
     * @dataProvider provideData
     */
    public function testMapKeys(array $expected, iterable $iterable, callable $function): void
    {
        $result = Iter\map_keys($iterable, $function);

        static::assertSame($expected, Iter\to_array_with_keys($result));
    }

    public function provideData(): iterable
    {
        yield [[1, 2, 3], [1, 2, 3], static fn (int $k): int => $k];
        yield [[1, 2 => 2, 4 => 3], [1, 2, 3], static fn (int $k): int => $k * 2];
        yield [['0' => 1, '1' => 2, '2' => 3], [1, 2, 3], static fn (int $k): string => (string) $k];
        yield [[], [], static fn (int $k): string => (string) $k];
        yield [[], Iter\Iterator::create([]), static fn (int $k): string => (string) $k];
        yield [['0' => 1, '1' => 2], Iter\Iterator::create([1, 2]), static fn (int $k): string => (string) $k];
    }
}
