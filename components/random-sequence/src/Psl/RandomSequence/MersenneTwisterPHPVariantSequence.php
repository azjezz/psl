<?php

declare(strict_types=1);

namespace Psl\RandomSequence;

use Override;

/**
 * A PRNG Based on the PHP variant of Mersenne Twister Algorithm.
 */
final class MersenneTwisterPHPVariantSequence implements SequenceInterface
{
    use Internal\MersenneTwisterTrait;

    /**
     * @pure
     */
    #[Override]
    protected function twist(int $m, int $u, int $v): int
    {
        return $m ^ (((($u & 0x8000_0000) | ($v & 0x7fff_ffff)) >> 1) & 0x7fff_ffff) ^ (0x9908_b0df * ($u & 1));
    }
}
