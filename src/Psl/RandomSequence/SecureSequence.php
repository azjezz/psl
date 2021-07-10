<?php

declare(strict_types=1);

namespace Psl\RandomSequence;

use Psl\SecureRandom;

/**
 * A Cryptographically Secure PRNG.
 */
final class SecureSequence implements SequenceInterface
{
    /**
     * Generates the next pseudorandom number.
     */
    public function next(): int
    {
        /** @psalm-suppress MissingThrowsDocblock */
        return SecureRandom\int();
    }
}
