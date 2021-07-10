<?php

declare(strict_types=1);

namespace Psl\RandomSequence;

/**
 * A Mersenne Twister ( MT19937 ) PRNG.
 */
final class MersenneTwisterSequence implements SequenceInterface
{
    use Internal\MersenneTwisterTrait;

    /**
     * @pure
     */
    protected function twist(int $m, int $u, int $v): int
    {
        return $m ^ (((($u & 0x80000000) | ($v & 0x7fffffff)) >> 1) & 0x7fffffff) ^ (0x9908b0df * ($v & 1));
    }
}
