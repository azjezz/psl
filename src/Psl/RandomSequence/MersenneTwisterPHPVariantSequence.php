<?php

declare(strict_types=1);

namespace Psl\RandomSequence;

/**
 * A PRNG Based on the PHP variant of Mersenne Twister Algorithm.
 */
final class MersenneTwisterPHPVariantSequence implements SequenceInterface
{
    use Internal\MersenneTwisterTrait;

    /**
     * @pure
     */
    protected function twist(int $m, int $u, int $v): int
    {
        return $m ^ (((($u & 0x80000000) | ($v & 0x7fffffff)) >> 1) & 0x7fffffff) ^ (0x9908b0df * ($u & 1));
    }
}
