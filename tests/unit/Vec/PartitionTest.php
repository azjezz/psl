<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Vec;

use PHPUnit\Framework\TestCase;
use Psl\Fun;
use Psl\Vec;

final class PartitionTest extends TestCase
{
    public function testPartition(): void
    {
        static::assertSame([[1, 2, 3, 4], [0]], Vec\partition([0, 1, 2, 3, 4], Fun\identity()));
    }
}
