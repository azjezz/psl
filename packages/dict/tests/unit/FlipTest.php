<?php

declare(strict_types=1);

namespace Psl\Dict\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Dict;
use Psl\Iter;

final class FlipTest extends TestCase
{
    public function testFlip(): void
    {
        $iterable = Iter\Iterator::create(['a' => 'x', 'b' => 'y']);

        $result = Dict\flip($iterable);

        static::assertSame(['x' => 'a', 'y' => 'b'], $result);
    }

    public function testFlipWithArray(): void
    {
        $result = Dict\flip(['a' => 'x', 'b' => 'y']);

        static::assertSame(['x' => 'a', 'y' => 'b'], $result);
    }
}
