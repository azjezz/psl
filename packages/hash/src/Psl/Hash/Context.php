<?php

declare(strict_types=1);

namespace Psl\Hash;

use HashContext;

use function hash_copy;
use function hash_final;
use function hash_init;
use function hash_update;

use const HASH_HMAC;

/**
 * Incremental hashing context.
 *
 * Example:
 *
 *      Hash\Context::forAlgorithm(Hash\Algorithm::Md5)
 *          ->update('The quick brown fox ')
 *          ->update('jumped over the lazy dog.')
 *          ->finalize()
 *      => Str("5c6ffbdd40d9556b73a21e63c3e0e904")
 *
 * @psalm-immutable
 */
final readonly class Context
{
    /**
     * @pure
     */
    private function __construct(
        private HashContext $internalContext,
    ) {}

    /**
     * Initialize an incremental hashing context.
     *
     * @pure
     */
    public static function forAlgorithm(Algorithm $algorithm): Context
    {
        $internalContext = hash_init($algorithm->value);

        return new self($internalContext);
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
        $internalContext = hash_init($algorithm->value, HASH_HMAC, $key);

        return new self($internalContext);
    }

    /**
     * Pump data into an active hashing context.
     *
     * @throws Exception\RuntimeException If unable to pump data into the active hashing context.
     *
     * @psalm-mutation-free
     */
    public function update(string $data): Context
    {
        $internalContext = hash_copy($this->internalContext);

        // @codeCoverageIgnoreStart
        if (!hash_update($internalContext, $data)) {
            throw new Exception\RuntimeException('Unable to pump data into the active hashing context.');
        }

        // @codeCoverageIgnoreEnd

        return new self($internalContext);
    }

    /**
     * Finalize an incremental hash and return resulting digest.
     *
     * @psalm-mutation-free
     */
    public function finalize(): string
    {
        $internalContext = hash_copy($this->internalContext);

        return hash_final($internalContext, false);
    }
}
