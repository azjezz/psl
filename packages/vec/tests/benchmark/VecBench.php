<?php

declare(strict_types=1);

namespace Psl\Vec\Tests\Benchmark;

use ArrayIterator;
use PhpBench\Attributes\Groups;
use PhpBench\Attributes\ParamProviders;
use Psl\Vec;

use function count;

#[Groups(['vec'])]
final class VecBench
{
    /**
     * @param array{data: list<int>} $params
     */
    #[ParamProviders('provideArrayData')]
    public function benchMap(array $params): void
    {
        Vec\map($params['data'], static fn(int $v): int => $v * 2);
    }

    /**
     * @param array{data: ArrayIterator<int, int>} $params
     */
    #[ParamProviders('provideIterableData')]
    public function benchMapIterable(array $params): void
    {
        Vec\map($params['data'], static fn(int $v): int => $v * 2);
    }

    /**
     * @param array{data: list<int>} $params
     */
    #[ParamProviders('provideArrayData')]
    public function benchFilter(array $params): void
    {
        Vec\filter($params['data'], static fn(int $v): bool => ($v % 2) === 0);
    }

    /**
     * @param array{data: ArrayIterator<int, int>} $params
     */
    #[ParamProviders('provideIterableData')]
    public function benchFilterIterable(array $params): void
    {
        Vec\filter($params['data'], static fn(int $v): bool => ($v % 2) === 0);
    }

    /**
     * @param array{data: list<int>} $params
     */
    #[ParamProviders('provideUniqueByData')]
    public function benchUniqueBy(array $params): void
    {
        Vec\unique_by($params['data'], static fn(int $v): int => $v % 50);
    }

    /**
     * @param array{data: list<int>} $params
     */
    #[ParamProviders('provideArrayData')]
    public function benchSort(array $params): void
    {
        Vec\sort($params['data']);
    }

    /**
     * @param array{data: list<int>} $params
     */
    #[ParamProviders('provideArrayData')]
    public function benchSortBy(array $params): void
    {
        Vec\sort_by($params['data'], static fn(int $v): int => -$v);
    }

    /**
     * @param array{data: list<int>} $params
     */
    #[ParamProviders('provideArrayData')]
    public function benchFlatMap(array $params): void
    {
        Vec\flat_map($params['data'], static fn(int $v): array => [$v, $v * 2]);
    }

    /**
     * @param array{data: list<int>} $params
     */
    #[ParamProviders('provideArrayData')]
    public function benchChunk(array $params): void
    {
        $_ = Vec\chunk($params['data'], 10);
    }

    /**
     * @param array{data: list<int>} $params
     */
    #[ParamProviders('provideArrayData')]
    public function benchReverse(array $params): void
    {
        Vec\reverse($params['data']);
    }

    /**
     * @param array{data: list<int>} $params
     */
    #[ParamProviders('provideArrayData')]
    public function benchFill(array $params): void
    {
        $_ = Vec\fill(count($params['data']), 42);
    }

    /**
     * @param array{data: list<int>} $params
     */
    #[ParamProviders('provideArrayData')]
    public function benchZip(array $params): void
    {
        Vec\zip($params['data'], $params['data']);
    }

    /**
     * @param array{data: list<int>} $params
     */
    #[ParamProviders('provideArrayData')]
    public function benchConcat(array $params): void
    {
        Vec\concat($params['data'], $params['data'], $params['data']);
    }

    /**
     * @param array{data: list<int>} $params
     */
    #[ParamProviders('provideArrayData')]
    public function benchSlice(array $params): void
    {
        $size = count($params['data']);
        /** @var non-negative-int $start */
        $start = (int) ($size / 4);
        /** @var non-negative-int $length */
        $length = (int) ($size / 2);
        $_ = Vec\slice($params['data'], $start, $length);
    }

    /**
     * @param array{data: list<int>} $params
     */
    #[ParamProviders('provideArrayData')]
    public function benchFlatten(array $params): void
    {
        $_ = Vec\flatten([$params['data'], $params['data'], $params['data']]);
    }

    /**
     * @param array{data: list<int|null>} $params
     */
    #[ParamProviders('provideNullableData')]
    public function benchFilterNulls(array $params): void
    {
        Vec\filter_nulls($params['data']);
    }

    /**
     * @return iterable<non-empty-string, array{data: list<int>}>
     */
    public function provideArrayData(): iterable
    {
        yield 'small (10)' => ['data' => Vec\range(1, 10)];
        yield 'medium (100)' => ['data' => Vec\range(1, 100)];
        yield 'large (1000)' => ['data' => Vec\range(1, 1000)];
    }

    /**
     * @return iterable<non-empty-string, array{data: ArrayIterator<int, int>}>
     */
    public function provideIterableData(): iterable
    {
        yield 'small (10)' => ['data' => new ArrayIterator(Vec\range(1, 10))];
        yield 'medium (100)' => ['data' => new ArrayIterator(Vec\range(1, 100))];
        yield 'large (1000)' => ['data' => new ArrayIterator(Vec\range(1, 1000))];
    }

    /**
     * @return iterable<non-empty-string, array{data: list<int>}>
     */
    public function provideUniqueByData(): iterable
    {
        yield 'small (10)' => ['data' => Vec\range(1, 10)];
        yield 'medium (100)' => ['data' => Vec\range(1, 100)];
        yield 'large (1000)' => ['data' => Vec\range(1, 1000)];
    }

    /**
     * @return iterable<non-empty-string, array{data: list<int|null>}>
     */
    public function provideNullableData(): iterable
    {
        $make = static function (int $size): array {
            $data = [];
            for ($i = 0; $i < $size; $i++) {
                $data[] = ($i % 3) === 0 ? null : $i;
            }

            return $data;
        };

        yield 'small (10)' => ['data' => $make(10)];
        yield 'medium (100)' => ['data' => $make(100)];
        yield 'large (1000)' => ['data' => $make(1000)];
    }
}
