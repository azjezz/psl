<?php

declare(strict_types=1);

namespace Psl\Dict\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Dict;

final class MapTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testMap(array $expected, iterable $iterable, callable $function): void
    {
        $result = Dict\map::<int, int, int|string>($iterable, $function);

        static::assertSame($expected, $result);
    }

    public static function provideData(): iterable
    {
        yield [[1, 2, 3], [1, 2, 3], static fn(int $v): int => $v];
        yield [[2, 4, 6], [1, 2, 3], static fn(int $v): int => $v * 2];
        yield [['1', '2', '3'], [1, 2, 3], static fn(int $v): string => (string) $v];
        yield [['1', '2', '3'], Collection\Map::<int, int>::fromArray([1, 2, 3]), static fn(int $v): string => (string) $v];
        yield [[], Collection\Map::<int, int>::fromArray([]), static fn(int $v): string => (string) $v];
        yield [[], [], static fn(int $v): string => (string) $v];
    }
}
