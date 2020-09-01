<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Str;

class PullWithKeyTest extends TestCase
{
    public function testPull(): void
    {
        $result = Iter\pull_with_key(
            Iter\range(0, 10),
            fn($k, $v) => Str\chr($v + $k + 65),
            fn($k, $v) => 2**($v+$k)
        );

        self::assertSame([
            1 => 'A', 4 => 'C', 16 => 'E', 64 => 'G', 256 => 'I', 1024 => 'K',
            4096 => 'M', 16384 => 'O', 65536 => 'Q', 262144 => 'S', 1048576 => 'U'
        ], Iter\to_array_with_keys($result));
    }
}
