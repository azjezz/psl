<?php

declare(strict_types=1);

namespace Psl\Vec\Tests\Unit;

use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Collection;
use Psl\Iter;
use Psl\Vec;

final class FilterNonnullByTest extends TestCase
{
    #[DataProvider('provideData')]
    public function testFilterNonnullBy(array $expected, iterable $iterable, Closure $function): void
    {
        $result = Vec\filter_nonnull_by($iterable, $function);

        static::assertSame($expected, $result);
    }

    public static function provideData(): iterable
    {
        yield [[], [], static fn(int $v): null|int => $v];
        yield [[1, 2, 3], [1, 2, 3], static fn(int $v): null|int => $v];
        yield [[2, 3], [1, 2, 3], static fn(int $v): null|int => $v > 1 ? $v : null];
        yield [['hello', 'world'], ['hello', '', 'world'], static fn(string $v): null|string => $v !== '' ? $v : null];
        yield [[], [null, null], static fn(null|string $v): null|string => $v];
        yield [[1, 2, 3], [1, 2, 3], static fn(int $v): int => 0];
        yield [
            [2, 3],
            Collection\Vector::fromArray([1, 2, 3]),
            static fn(int $v): null|int => $v > 1 ? $v : null,
        ];
        yield [
            [2, 4],
            Iter\Iterator::create([1, 2, 3, 4]),
            static fn(int $v): null|int => ($v % 2) === 0 ? $v : null,
        ];
    }
}
