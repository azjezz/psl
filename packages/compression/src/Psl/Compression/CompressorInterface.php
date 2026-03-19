<?php

declare(strict_types=1);

namespace Psl\Compression;

/**
 * Incremental compressor.
 *
 * Implementations compress data in chunks, allowing streaming compression
 * without buffering the entire input in memory.
 *
 * Compression is inherently stateful. A single compressor instance MUST NOT
 * be used concurrently across multiple fibers or streams. Each concurrent
 * compression stream requires its own instance.
 */
interface CompressorInterface
{
    /**
     * Incrementally compress a chunk of data.
     *
     * @throws Exception\RuntimeException If the compression operation fails.
     */
    public function push(string $data): string;

    /**
     * Finalize the compression stream and reset to initial state.
     *
     * After this call, the compressor is ready for a new stream;
     * subsequent calls to {@see push()} begin a fresh compression context.
     *
     * Implementations MUST re-initialize their internal state so the
     * instance can be reused without creating a new object.
     *
     * If finalization fails, implementations MUST NOT re-initialize,
     * allowing subsequent retry attempts.
     *
     * @throws Exception\RuntimeException If the compression operation fails.
     */
    public function finish(): string;
}
