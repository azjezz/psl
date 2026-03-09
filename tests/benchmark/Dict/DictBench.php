<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Dict;

use PhpBench\Attributes\Groups;
use PhpBench\Attributes\ParamProviders;
use Psl\Dict;
use Psl\Vec;

#[Groups(['dict'])]
final class DictBench
{
    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchUniqueBy(array $params): void
    {
        Dict\unique_by($params['data'], static fn(int $v): int => $v % 50);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchGroupBy(array $params): void
    {
        Dict\group_by($params['data'], static fn(int $v): int => $v % 10);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchMap(array $params): void
    {
        Dict\map($params['data'], static fn(int $v): int => $v * 2);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchMapKeys(array $params): void
    {
        Dict\map_keys($params['data'], static fn(int $k): string => 'key_' . $k);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchFilter(array $params): void
    {
        Dict\filter($params['data'], static fn(int $v): bool => ($v % 2) === 0);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchPull(array $params): void
    {
        Dict\pull($params['data'], static fn(int $v): int => $v * 2, static fn(int $v): string => 'k' . $v);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchSelectKeys(array $params): void
    {
        Dict\select_keys($params['data'], Vec\range(0, (int) (count($params['data']) / 2)));
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchEqual(array $params): void
    {
        Dict\equal($params['data'], $params['data']);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchSlice(array $params): void
    {
        $size = count($params['data']);
        /** @var non-negative-int $start */
        $start = (int) ($size / 4);
        /** @var non-negative-int $length */
        $length = (int) ($size / 2);
        $_ = Dict\slice($params['data'], $start, $length);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchFlip(array $params): void
    {
        $_ = Dict\flip($params['data']);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchMerge(array $params): void
    {
        Dict\merge($params['data'], $params['data'], $params['data']);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchDiff(array $params): void
    {
        Dict\diff($params['data'], $params['data']);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchIntersect(array $params): void
    {
        Dict\intersect($params['data'], $params['data']);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchFlatten(array $params): void
    {
        $_ = Dict\flatten([$params['data'], $params['data'], $params['data']]);
    }

    /**
     * @param array{data: array<int, int|null>} $params
     */
    #[ParamProviders('provideNullableData')]
    public function benchFilterNulls(array $params): void
    {
        Dict\filter_nulls($params['data']);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchSort(array $params): void
    {
        Dict\sort($params['data']);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchSortByKey(array $params): void
    {
        Dict\sort_by_key($params['data']);
    }

    /**
     * @return iterable<non-empty-string, array{data: array<int, int>}>
     */
    public function provideData(): iterable
    {
        yield 'small (10)' => ['data' => Vec\range(1, 10)];
        yield 'medium (100)' => ['data' => Vec\range(1, 100)];
        yield 'large (1000)' => ['data' => Vec\range(1, 1000)];
    }

    /**
     * @return iterable<non-empty-string, array{data: array<int, int|null>}>
     */
    public function provideNullableData(): iterable
    {
        $make = static function (int $size): array {
            $data = [];
            for ($i = 0; $i < $size; $i++) {
                $data[$i] = ($i % 3) === 0 ? null : $i;
            }

            return $data;
        };

        yield 'small (10)' => ['data' => $make(10)];
        yield 'medium (100)' => ['data' => $make(100)];
        yield 'large (1000)' => ['data' => $make(1000)];
    }
}
