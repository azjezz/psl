<?php

declare(strict_types=1);

namespace Psl\Math\Tests\Benchmark;

use PhpBench\Attributes\Groups;
use PhpBench\Attributes\ParamProviders;
use Psl\Math;
use Psl\Vec;

#[Groups(['math'])]
final class MathBench
{
    /**
     * @param array{data: list<int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchSum(array $params): void
    {
        $_ = Math\sum($params['data']);
    }

    /**
     * @param array{data: list<int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchSumFloats(array $params): void
    {
        $_ = Math\sum_floats($params['data']);
    }

    /**
     * @param array{data: list<int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchMax(array $params): void
    {
        $_ = Math\max($params['data']);
    }

    /**
     * @param array{data: list<int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchMin(array $params): void
    {
        $_ = Math\min($params['data']);
    }

    /**
     * @param array{data: list<int>} $params
     */
    #[ParamProviders('provideData')]
    public function benchMean(array $params): void
    {
        $_ = Math\mean($params['data']);
    }

    /**
     * @return iterable<non-empty-string, array{data: list<int>}>
     */
    public function provideData(): iterable
    {
        yield 'small (10)' => ['data' => Vec\range(1, 10)];
        yield 'medium (100)' => ['data' => Vec\range(1, 100)];
        yield 'large (1000)' => ['data' => Vec\range(1, 1000)];
    }
}
