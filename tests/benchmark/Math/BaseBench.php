<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Math;

use PhpBench\Attributes\Groups;
use Psl;
use Psl\Math;

#[Groups(['math'])]
final class BaseBench
{
    /**
     * @throws Psl\Exception\InvariantViolationException
     * @throws Psl\Math\Exception\InvalidArgumentException
     * @throws Psl\Math\Exception\OverflowException
     */
    public function benchFromBase(): void
    {
        $result = Math\from_base("1y2p0ij32e8e7", 36);

        Psl\invariant($result === Math\INT64_MAX, "expected: `Math\INT64_MAX`, got `$result`.");
    }

    /**
     * @throws Psl\Exception\InvariantViolationException
     * @throws Psl\Math\Exception\InvalidArgumentException
     */
    public function benchToBase(): void
    {
        $result = Math\to_base(Math\INT64_MAX, 36);

        Psl\invariant($result === "1y2p0ij32e8e7", "expected: `\"1y2p0ij32e8e7\"`, got `\"$result\"`.");
    }
}
