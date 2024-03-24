<?php

declare(strict_types=1);

namespace Psl\RandomSequence;

use Psl\Default\DefaultInterface;
use Psl\SecureRandom;

/**
 * A Cryptographically Secure PRNG.
 *
 * @immutable
 */
final class SecureSequence implements DefaultInterface, SequenceInterface
{
    /**
     * @pure
     */
    public static function default(): static
    {
        return new self();
    }

    /**
     * Generates the next pseudorandom number.
     *
     * @external-mutation-free
     */
    public function next(): int
    {
        /** @psalm-suppress MissingThrowsDocblock */
        return SecureRandom\int();
    }
}
