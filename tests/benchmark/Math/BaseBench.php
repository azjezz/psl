<?php

declare(strict_types=1);

namespace Psl\Tests\Benchmark\Math;

use PhpBench\Attributes\Groups;
use Psl\Math;
use Psl;

#[Groups(['math'])]
final class BaseBench
{
    public function benchFromBase(): void
    {
        $result = Math\from_base("1y2p0ij32e8e7", 36);

        Psl\invariant($result === Math\INT64_MAX, "expected: `Math\INT64_MAX`, got `%d`.", $result);
    }

    public function benchFromBaseFfi(): void
    {
        $result = Math\from_base_ffi("1y2p0ij32e8e7", 36);

        Psl\invariant($result === Math\INT64_MAX, "expected: `Math\INT64_MAX`, got `%d`.", $result);
    }

    public function benchToBase(): void
    {
        $result = Math\to_base(Math\INT64_MAX, 36);

        Psl\invariant($result === "1y2p0ij32e8e7", "expected: `\"1y2p0ij32e8e7\"`, got `\"%s\"`.", $result);
    }

    public function benchToBaseFfi(): void
    {
        $result = Math\to_base_ffi(Math\INT64_MAX, 36);

        Psl\invariant($result === "1y2p0ij32e8e7", "expected: `\"1y2p0ij32e8e7\"`, got `\"%s\"`.", $result);
    }
}
