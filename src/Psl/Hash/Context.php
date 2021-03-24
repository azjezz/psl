<?php

declare(strict_types=1);

namespace Psl\Hash;

use HashContext;
use Psl;
use Psl\Iter;
use Psl\Str;

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
     * @throws Psl\Exception\InvariantViolationException If the given algorithm is unsupported.
     *
     * @pure
     */
    public static function forAlgorithm(string $algorithm): Context
    {
        /** @psalm-suppress ImpureFunctionCall */
        Psl\invariant(
            Iter\contains(algorithms(), $algorithm),
            'Expected a valid hashing algorithm, "%s" given.',
            $algorithm,
        );
        $internal_context = hash_init($algorithm);

        return new self($internal_context);
    }

    /**
     * Initialize an incremental HMAC hashing context.
     *
     * @throws Psl\Exception\InvariantViolationException If the given algorithm is unsupported.
     *
     * @pure
     */
    public static function hmac(string $algorithm, string $key): Context
    {
        /** @psalm-suppress ImpureFunctionCall */
        Psl\invariant(
            Iter\contains(Hmac\algorithms(), $algorithm),
            'Expected a hashing algorithms suitable for HMAC, "%s" given.',
            $algorithm
        );

        Psl\invariant(!Str\is_empty($key), 'Expected a non-empty shared secret key.');

        $internal_context = hash_init($algorithm, HASH_HMAC, $key);

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
