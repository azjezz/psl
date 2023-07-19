<?php

declare(strict_types=1);

namespace Psl\RandomSequence\Internal;

/**
 * @internal
 */
trait MersenneTwisterTrait
{
    /**
     * @var non-empty-array<int, int>
     */
    private array $state;

    private int $index;

    final public function __construct(
        int $seed
    ) {
        $state = [$seed & 0xffffffff];
        /** @var array{0: int, 1: int} $i */
        $i = [$seed & 0xffff, ($seed >> 16) & 0xffff];

        for ($index = 1; $index < 624; $index++) {
            $i[0] ^= $i[1] >> 14;

            $carry = (0x8965 * $i[0]) + $index;
            $i[1] = ((0x8965 * $i[1]) + (0x6C07 * $i[0]) + ($carry >> 16)) & 0xffff;
            $i[0] = $carry & 0xffff;

            $state[$index] = ($i[1] << 16) | $i[0];
        }

        $this->state = $state;
        $this->index = $index;
    }

    /**
     * Generates the next pseudorandom number.
     *
     * @psalm-external-mutation-free
     */
    final public function next(): int
    {
        if ($this->index >= 624) {
            $state = $this->state;
            for ($i = 0; $i < 227; $i++) {
                $state[$i] = $this->twist($state[$i + 397], $state[$i], $state[$i + 1]);
            }
            for (; $i < 623; $i++) {
                $state[$i] = $this->twist($state[$i - 227], $state[$i], $state[$i + 1]);
            }
            $state[623] = $this->twist($state[396], $state[623], $state[0]);
            $this->state = $state;

            $this->index = 0;
        }

        $y = $this->state[$this->index++];

        $y ^= ($y >> 11) & 0x001fffff;
        $y ^= ($y <<  7) & 0x9d2c5680;
        $y ^= ($y << 15) & 0xefc60000;
        $y ^= ($y >> 18) & 0x00003fff;

        return ($y >> 1) & 0x7fffffff;
    }

    /**
     * @pure
     */
    abstract protected function twist(int $m, int $u, int $v): int;
}
