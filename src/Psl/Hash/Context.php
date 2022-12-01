<?php

declare(strict_types=1);

namespace Psl\Hash;

use HashContext;

use function hash_final;
use function hash_init;
use function hash_update;

use const HASH_HMAC;

/**
 * Incremental hashing context.
 *
 * Example:
 *
 *      Hash\Context::forAlgorithm('md5')
 *          ->update('The quick brown fox ')
 *          ->update('jumped over the lazy dog.')
 *          ->finalize()
 *      => Str("5c6ffbdd40d9556b73a21e63c3e0e904")
 *
 * @psalm-immutable
 */
final class Context
{
    private HashContext $internalContext;

    private function __construct(HashContext $internal_context)
    {
        $this->internalContext = $internal_context;
    }

    /**
     * Initialize an incremental hashing context.
     *
     * @pure
     */
    public static function forAlgorithm(Algorithm $algorithm): Context
    {
        $internal_context = hash_init($algorithm->value);

        return new self($internal_context);
    }

    /**
     * Initialize an incremental HMAC hashing context.
     *
     * @param non-empty-string $key
     *
     * @pure
     */
    public static function hmac(Hmac\Algorithm $algorithm, string $key): Context
    {
        $internal_context = hash_init($algorithm->value, HASH_HMAC, $key);

        return new self($internal_context);
    }

    /**
     * Pump data into an active hashing context.
     *
     * @psalm-mutation-free
     *
     * @throws Exception\RuntimeException If unable to pump data into the active hashing context.
     */
    public function update(string $data): Context
    {
        $internal_context = hash_copy($this->internalContext);

        // @codeCoverageIgnoreStart
        /** @psalm-suppress ImpureFunctionCall - it creates a copy of the context, so we can consider it pure! */
        if (!hash_update($internal_context, $data)) {
            throw new Exception\RuntimeException('Unable to pump data into the active hashing context.');
        }
        // @codeCoverageIgnoreEnd

        return new self($internal_context);
    }

    /**
     * Finalize an incremental hash and return resulting digest.
     *
     * @psalm-mutation-free
     */
    public function finalize(): string
    {
        $internal_context = hash_copy($this->internalContext);

        return hash_final($internal_context, false);
    }
}
