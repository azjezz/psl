<?php

declare(strict_types=1);

namespace Psl\Dict\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Dict;
use Psl\Str;
use Psl\Vec;

final class PullWithKeyTest extends TestCase
{
    public function testPull(): void
    {
        $result = Dict\pull_with_key::<int, int, int, string>(
            Vec\range::<int>(0, 10),
            static fn(int $k, int $v): string => Str\chr($v + $k + 65),
            static fn(int $k, int $v): int => 2 ** ($v + $k),
        );

        static::assertSame(
            [
                1 => 'A',
                4 => 'C',
                16 => 'E',
                64 => 'G',
                256 => 'I',
                1024 => 'K',
                4096 => 'M',
                16_384 => 'O',
                65_536 => 'Q',
                262_144 => 'S',
                1_048_576 => 'U',
            ],
            $result,
        );
    }
}
