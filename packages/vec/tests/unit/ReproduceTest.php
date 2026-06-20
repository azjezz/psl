<?php

declare(strict_types=1);

namespace Psl\Vec\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\Fun;
use Psl\Vec;

final class ReproduceTest extends TestCase
{
    public function testReproduce(): void
    {
        static::assertSame([1], Vec\reproduce::<int>(1, Fun\identity::<int>()));
        static::assertSame([1, 2, 3], Vec\reproduce::<int>(3, Fun\identity::<int>()));
    }
}
