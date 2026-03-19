<?php

declare(strict_types=1);

namespace Psl\Compression;

/**
 * Incremental decompressor.
 *
 * Implementations decompress data in chunks, allowing streaming decompression
 * without buffering the entire input in memory.
 *
 * Decompression is inherently stateful. A single decompressor instance MUST NOT
 * be used concurrently across multiple fibers or streams. Each concurrent
 * decompression stream requires its own instance.
 */
interface DecompressorInterface
{
    /**
     * Incrementally decompress a chunk of data.
     *
     * @throws Exception\RuntimeException If the decompression operation fails.
     */
    public function push(string $data): string;

    /**
     * Finalize the decompression stream and reset to initial state.
     *
     * After this call, the decompressor is ready for a new stream;
     * subsequent calls to {@see push()} begin a fresh decompression context.
     *
     * Implementations MUST re-initialize their internal state so the
     * instance can be reused without creating a new object.
     *
     * If finalization fails, implementations MUST NOT re-initialize,
     * allowing subsequent retry attempts.
     *
     * @throws Exception\RuntimeException If the decompression operation fails.
     */
    public function finish(): string;
}
