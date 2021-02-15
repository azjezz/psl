<?php

declare(strict_types=1);

namespace Psl\Tests\Dict;

use PHPUnit\Framework\TestCase;
use Psl\Dict;
use Psl\Str;
use Psl\Vec;

final class PullWithKeyTest extends TestCase
{
    public function testPull(): void
    {
        $result = Dict\pull_with_key(
            Vec\range(0, 10),
            static fn ($k, $v) => Str\chr($v + $k + 65),
            static fn ($k, $v) => 2 ** ($v + $k)
        );

        static::assertSame([
            1 => 'A', 4 => 'C', 16 => 'E', 64 => 'G', 256 => 'I', 1024 => 'K',
            4096 => 'M', 16384 => 'O', 65536 => 'Q', 262144 => 'S', 1048576 => 'U'
        ], $result);
    }
}
