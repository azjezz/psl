<?php

declare(strict_types=1);

namespace Psl\Dict\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Dict;

final class FlattenTest extends TestCase
{
    public function testFlattenEmpty(): void
    {
        static::assertSame([], Dict\flatten([]));
    }

    public function testFlattenAllArrays(): void
    {
        static::assertSame(['a' => 1, 'b' => 2, 'c' => 3], Dict\flatten([['a' => 1], ['b' => 2, 'c' => 3]]));
    }
}
