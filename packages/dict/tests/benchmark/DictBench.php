<?php

declare(strict_types=1);

namespace Psl\Dict\Tests\Benchmark;

use PhpBench\Attributes\Groups;
use PhpBench\Attributes\ParamProviders;
use Psl\Dict;
use Psl\Vec;

use function count;

#[Groups(['dict'])]
final class DictBench
{
    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchUniqueBy(array $params): void
    {
        Dict\unique_by::<int, int, int>($params['data'], static fn(int $v): int => $v % 50);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchGroupBy(array $params): void
    {
        Dict\group_by::<int, int>($params['data'], static fn(int $v): int => $v % 10);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchMap(array $params): void
    {
        Dict\map::<int, int, int>($params['data'], static fn(int $v): int => $v * 2);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchMapKeys(array $params): void
    {
        Dict\map_keys::<int, string, int>($params['data'], static fn(int $k): string => 'key_' . $k);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchFilter(array $params): void
    {
        Dict\filter::<int, int>($params['data'], static fn(int $v): bool => ($v % 2) === 0);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchPull(array $params): void
    {
        Dict\pull::<int, int, string, int>($params['data'], static fn(int $v): int => $v * 2, static fn(int $v): string => 'k' . $v);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchSelectKeys(array $params): void
    {
        Dict\select_keys::<int, int>($params['data'], Vec\range::<int>(0, (int) (count($params['data']) / 2)));
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchEqual(array $params): void
    {
        Dict\equal::<int, int>($params['data'], $params['data']);
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
        $_ = Dict\slice::<int, int>($params['data'], $start, $length);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchFlip(array $params): void
    {
        $_ = Dict\flip::<int, int>($params['data']);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchMerge(array $params): void
    {
        Dict\merge::<int, int>($params['data'], $params['data'], $params['data']);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchDiff(array $params): void
    {
        Dict\diff::<int, int>($params['data'], $params['data']);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchIntersect(array $params): void
    {
        Dict\intersect::<int, int>($params['data'], $params['data']);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchFlatten(array $params): void
    {
        $_ = Dict\flatten::<int, int>([$params['data'], $params['data'], $params['data']]);
    }

    /**
     * @param array{data: array<int, int|null>} $params
     */
    #[ParamProviders('provideNullableData')]
    public function benchFilterNulls(array $params): void
    {
        Dict\filter_nulls::<int, int>($params['data']);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchSort(array $params): void
    {
        Dict\sort::<int, int>($params['data']);
    }

    /**
     * @param array{data: array<int, int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchSortByKey(array $params): void
    {
        Dict\sort_by_key::<int, int>($params['data']);
    }

    /**
     * @return iterable<non-empty-string, array{data: array<int, int>}>
     */
    public function provideData(): iterable
    {
        yield 'small (10)' => ['data' => Vec\range::<int>(1, 10)];
        yield 'medium (100)' => ['data' => Vec\range::<int>(1, 100)];
        yield 'large (1000)' => ['data' => Vec\range::<int>(1, 1000)];
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
