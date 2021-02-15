<?php

declare(strict_types=1);

namespace Psl\Tests\Dict;

use PHPUnit\Framework\TestCase;
use Psl\Dict;
use Psl\Str;
use Psl\Vec;

final class PullTest extends TestCase
{
    public function testPull(): void
    {
        $result = Dict\pull(
            Vec\range(0, 10),
            static fn ($i) => Str\chr($i + 65),
            static fn ($i) => 2 ** $i
        );

        static::assertSame([
            1 => 'A', 2 => 'B', 4 => 'C', 8 => 'D', 16 => 'E', 32 => 'F',
            64 => 'G', 128 => 'H', 256 => 'I', 512 => 'J', 1024 => 'K'
        ], $result);
    }
}
