<?php

declare(strict_types=1);

namespace Psl\RandomSequence;

use Override;

/**
 * A Mersenne Twister ( MT19937 ) PRNG.
 */
final class MersenneTwisterSequence implements SequenceInterface
{
    use Internal\MersenneTwisterTrait;

    /**
     * @pure
     */
    #[Override]
    protected function twist(int $m, int $u, int $v): int
    {
        return $m ^ (((($u & 0x8000_0000) | ($v & 0x7fff_ffff)) >> 1) & 0x7fff_ffff) ^ (0x9908_b0df * ($v & 1));
    }
}
