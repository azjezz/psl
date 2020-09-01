<?php

declare(strict_types=1);

namespace Psl\Tests\Iter;

use PHPUnit\Framework\TestCase;
use Psl\Iter;
use Psl\Str;

class PullTest extends TestCase
{
    public function testPull(): void
    {
        $result = Iter\pull(
            Iter\range(0, 10),
            fn ($i) => Str\chr($i + 65),
            fn ($i) => 2 ** $i
        );

        self::assertSame([
            1 => 'A', 2 => 'B', 4 => 'C', 8 => 'D', 16 => 'E', 32 => 'F',
            64 => 'G', 128 => 'H', 256 => 'I', 512 => 'J', 1024 => 'K'
        ], Iter\to_array_with_keys($result));
    }
}
