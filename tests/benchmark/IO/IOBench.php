<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\IO;

use PhpBench\Attributes\Groups;
use PhpBench\Attributes\ParamProviders;
use Psl\IO\MemoryHandle;

#[Groups(['io'])]
final class IOBench
{
    /**
     * @param array{chunk_size: int, iterations: int} $params
     */
    #[ParamProviders('provideWriteData')]
    public function benchMemoryHandleWriteAppend(array $params): void
    {
        $handle = new MemoryHandle();
        $chunk = str_repeat('x', $params['chunk_size']);
        for ($i = 0; $i < $params['iterations']; $i++) {
            $handle->tryWrite($chunk);
        }
    }

    /**
     * @param array{chunk_size: non-negative-int, iterations: int} $params
     */
    #[ParamProviders('provideWriteData')]
    public function benchMemoryHandleWriteOverwrite(array $params): void
    {
        $initial = str_repeat('y', $params['chunk_size'] * $params['iterations']);
        $handle = new MemoryHandle($initial);
        $chunk = str_repeat('x', $params['chunk_size']);
        for ($i = 0; $i < $params['iterations']; $i++) {
            /** @var non-negative-int $offset */
            $offset = $i * $params['chunk_size'];
            $handle->seek($offset);
            $handle->tryWrite($chunk);
        }
    }

    /**
     * @param array{chunk_size: int, iterations: int} $params
     */
    #[ParamProviders('provideWriteData')]
    public function benchMemoryHandleReadAll(array $params): void
    {
        $data = str_repeat('x', $params['chunk_size'] * $params['iterations']);
        $handle = new MemoryHandle($data);
        $handle->readAll();
    }

    /**
     * @return iterable<string, array{chunk_size: int, iterations: int}>
     */
    public function provideWriteData(): iterable
    {
        yield 'small_chunks' => ['chunk_size' => 64, 'iterations' => 100];
        yield 'medium_chunks' => ['chunk_size' => 1024, 'iterations' => 100];
        yield 'large_chunks' => ['chunk_size' => 8192, 'iterations' => 50];
    }
}
