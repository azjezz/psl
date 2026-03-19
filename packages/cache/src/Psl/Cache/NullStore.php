<?php

declare(strict_types=1);

namespace Psl\Cache;

use Closure;
use Override;
use Psl\DateTime\Duration;

/**
 * A cache store that never stores anything.
 *
 * Every call to {@see compute()} invokes the computer. Every call to
 * {@see get()} throws. Useful for testing, development, or disabling
 * caching without changing calling code.
 */
final class NullStore implements StoreInterface
{
    /**
     * Always throws - NullStore does not store entries.
     *
     * @param non-empty-string $key Ignored.
     *
     * @throws Exception\UnavailableItemException Always.
     */
    #[Override]
    public function get(string $key): never
    {
        throw new Exception\UnavailableItemException('NullStore does not store entries.');
    }

    /**
     * Always invokes the computer and returns its result.
     *
     * Nothing is cached - every call recomputes. The $key and $ttl
     * parameters are ignored.
     *
     * @template T
     *
     * @param non-empty-string $key Ignored.
     * @param (Closure(): T) $computer Always invoked.
     * @param null|Duration $ttl Ignored.
     *
     * @return T
     */
    #[Override]
    public function compute(string $key, Closure $computer, null|Duration $ttl = null): mixed
    {
        return $computer();
    }

    /**
     * Always invokes the computer with null and returns its result.
     *
     * Nothing is cached and no previous value exists, so $computer
     * always receives null as the old value.
     *
     * @template T
     *
     * @param non-empty-string $key Ignored.
     * @param (Closure(null|T): T) $computer Always invoked with null.
     * @param null|Duration $ttl Ignored.
     *
     * @return T
     */
    #[Override]
    public function update(string $key, Closure $computer, null|Duration $ttl = null): mixed
    {
        return $computer(null);
    }

    /**
     * No-op - nothing to delete since nothing is stored.
     *
     * @param non-empty-string $key Ignored.
     */
    #[Override]
    public function delete(string $key): void {}
}
