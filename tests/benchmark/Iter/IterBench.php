<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Iter;

use ArrayIterator;
use PhpBench\Attributes\Groups;
use PhpBench\Attributes\ParamProviders;
use Psl\Iter;
use Psl\Vec;

#[Groups(['iter'])]
final class IterBench
{
    /**
     * @param array{data: list<int>, target: int} $params
     */
    #[ParamProviders('provideContainsData')]
    public function benchContainsArray(array $params): void
    {
        $_ = Iter\contains($params['data'], $params['target']);
    }

    /**
     * @param array{data: ArrayIterator<int, int>, target: int} $params
     */
    #[ParamProviders('provideContainsIterableData')]
    public function benchContainsIterable(array $params): void
    {
        $_ = Iter\contains($params['data'], $params['target']);
    }

    /**
     * @param array{data: array<int, int>, target: int} $params
     */
    #[ParamProviders('provideContainsKeyData')]
    public function benchContainsKeyArray(array $params): void
    {
        $_ = Iter\contains_key($params['data'], $params['target']);
    }

    /**
     * @param array{data: ArrayIterator<int, int>, target: int} $params
     */
    #[ParamProviders('provideContainsKeyIterableData')]
    public function benchContainsKeyIterable(array $params): void
    {
        $_ = Iter\contains_key($params['data'], $params['target']);
    }

    /**
     * @param array{data: list<int>} $params
     */
    #[ParamProviders('provideArrayData')]
    public function benchCount(array $params): void
    {
        $_ = Iter\count($params['data']);
    }

    /**
     * @param array{data: list<int>} $params
     */
    #[ParamProviders('provideArrayData')]
    public function benchFirst(array $params): void
    {
        $_ = Iter\first($params['data']);
    }

    /**
     * @param array{data: list<int>} $params
     */
    #[ParamProviders('provideArrayData')]
    public function benchFirstKey(array $params): void
    {
        $_ = Iter\first_key($params['data']);
    }

    /**
     * @param array{data: list<int>} $params
     */
    #[ParamProviders('provideArrayData')]
    public function benchLastKey(array $params): void
    {
        $_ = Iter\last_key($params['data']);
    }

    /**
     * @param array{data: list<int>} $params
     */
    #[ParamProviders('provideArrayData')]
    public function benchLast(array $params): void
    {
        $_ = Iter\last($params['data']);
    }

    /**
     * @param array{data: list<int>} $params
     */
    #[ParamProviders('provideArrayData')]
    public function benchIsEmpty(array $params): void
    {
        $_ = Iter\is_empty($params['data']);
    }

    /**
     * @param array{data: list<int>} $params
     */
    #[ParamProviders('provideArrayData')]
    public function benchReduce(array $params): void
    {
        $_ = Iter\reduce($params['data'], static fn(int $acc, int $v): int => $acc + $v, 0);
    }

    /**
     * @return iterable<non-empty-string, array{data: list<int>, target: int}>
     */
    public function provideContainsData(): iterable
    {
        $large = Vec\range(1, 1000);

        yield 'small, start' => ['data' => Vec\range(1, 10), 'target' => 1];
        yield 'small, end' => ['data' => Vec\range(1, 10), 'target' => 10];
        yield 'small, missing' => ['data' => Vec\range(1, 10), 'target' => 99];
        yield 'large, start' => ['data' => $large, 'target' => 1];
        yield 'large, end' => ['data' => $large, 'target' => 1000];
        yield 'large, missing' => ['data' => $large, 'target' => 9999];
    }

    /**
     * @return iterable<non-empty-string, array{data: ArrayIterator<int, int>, target: int}>
     */
    public function provideContainsIterableData(): iterable
    {
        yield 'small, start' => ['data' => new ArrayIterator(Vec\range(1, 10)), 'target' => 1];
        yield 'large, end' => ['data' => new ArrayIterator(Vec\range(1, 1000)), 'target' => 1000];
    }

    /**
     * @return iterable<non-empty-string, array{data: array<int, int>, target: int}>
     */
    public function provideContainsKeyData(): iterable
    {
        $large = Vec\range(1, 1000);

        yield 'small, start' => ['data' => Vec\range(1, 10), 'target' => 0];
        yield 'small, end' => ['data' => Vec\range(1, 10), 'target' => 9];
        yield 'small, missing' => ['data' => Vec\range(1, 10), 'target' => 99];
        yield 'large, start' => ['data' => $large, 'target' => 0];
        yield 'large, end' => ['data' => $large, 'target' => 999];
        yield 'large, missing' => ['data' => $large, 'target' => 9999];
    }

    /**
     * @return iterable<non-empty-string, array{data: ArrayIterator<int, int>, target: int}>
     */
    public function provideContainsKeyIterableData(): iterable
    {
        yield 'small, start' => ['data' => new ArrayIterator(Vec\range(1, 10)), 'target' => 0];
        yield 'large, end' => ['data' => new ArrayIterator(Vec\range(1, 1000)), 'target' => 999];
    }

    /**
     * @return iterable<non-empty-string, array{data: list<int>}>
     */
    public function provideArrayData(): iterable
    {
        yield 'small (10)' => ['data' => Vec\range(1, 10)];
        yield 'large (1000)' => ['data' => Vec\range(1, 1000)];
    }
}
