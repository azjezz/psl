<?php

declare(strict_types=1);

namespace Psl\RandomSequence;

use Psl\Default\DefaultInterface;
use Psl\SecureRandom;

/**
 * A Cryptographically Secure PRNG.
 */
final class SecureSequence implements DefaultInterface, SequenceInterface
{
    /**
     * Construct a new secure sequence.
     *
     * @pure
     */
    public function __construct()
    {
    }

    /**
     * @pure
     */
    #[\Override]
    public static function default(): static
    {
        return new self();
    }

    /**
     * Generates the next pseudorandom number.
     *
     * @psalm-external-mutation-free
     */
    #[\Override]
    public function next(): int
    {
        /**
         * @psalm-suppress MissingThrowsDocblock
         * @psalm-suppress ImpureFunctionCall
         */
        return SecureRandom\int();
    }
}
