<?php

declare(strict_types=1);

namespace Psl\Async\Tests\Benchmark;

use PhpBench\Attributes\Groups;
use PhpBench\Attributes\ParamProviders;
use Psl\Async;

#[Groups(['async'])]
final class AsyncBench
{
    /**
     * @param array{ops: int} $params
     */
    #[ParamProviders('provideSequenceData')]
    public function benchSequence(array $params): void
    {
        $sequence = new Async\Sequence(static fn(int $v): int => $v * 2);

        $awaitables = [];
        for ($i = 0; $i < $params['ops']; $i++) {
            $awaitables[] = Async\run(static fn(): int => $sequence->waitFor($i));
        }

        foreach ($awaitables as $awaitable) {
            $awaitable->await();
        }
    }

    /**
     * @param array{ops: int, concurrency: positive-int} $params
     */
    #[ParamProviders('provideSemaphoreData')]
    public function benchSemaphore(array $params): void
    {
        $semaphore = new Async\Semaphore($params['concurrency'], static fn(int $v): int => $v * 2);

        $awaitables = [];
        for ($i = 0; $i < $params['ops']; $i++) {
            $awaitables[] = Async\run(static fn(): int => $semaphore->waitFor($i));
        }

        foreach ($awaitables as $awaitable) {
            $awaitable->await();
        }
    }

    /**
     * @return iterable<string, array{ops: int}>
     */
    public function provideSequenceData(): iterable
    {
        yield 'few_ops' => ['ops' => 10];
        yield 'many_ops' => ['ops' => 100];
    }

    /**
     * @return iterable<string, array{ops: int, concurrency: positive-int}>
     */
    public function provideSemaphoreData(): iterable
    {
        yield 'low_concurrency' => ['ops' => 50, 'concurrency' => 2];
        yield 'high_concurrency' => ['ops' => 50, 'concurrency' => 10];
    }
}
