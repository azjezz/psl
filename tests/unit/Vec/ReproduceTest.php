<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Vec;

use PHPUnit\Framework\TestCase;
use Psl\Fun;
use Psl\Vec;

final class ReproduceTest extends TestCase
{
    public function testReproduce(): void
    {
        static::assertSame([1], Vec\reproduce(1, Fun\identity()));
        static::assertSame([1, 2, 3], Vec\reproduce(3, Fun\identity()));
    }
}
