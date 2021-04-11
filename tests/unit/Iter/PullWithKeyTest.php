<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Str;

final class PullWithKeyTest extends TestCase
{
    public function testPull(): void
    {
        $result = Iter\pull_with_key(
            Iter\range(0, 10),
            static fn ($k, $v) => Str\chr($v + $k + 65),
            static fn ($k, $v) => 2 ** ($v + $k)
        );

        static::assertSame([
            1 => 'A', 4 => 'C', 16 => 'E', 64 => 'G', 256 => 'I', 1024 => 'K',
            4096 => 'M', 16384 => 'O', 65536 => 'Q', 262144 => 'S', 1048576 => 'U'
        ], Iter\to_array_with_keys($result));
    }
}
