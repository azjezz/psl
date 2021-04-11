<?php

declare(strict_types=1);

namespace Psl\Tests\Vec;

use PHPUnit\Framework\TestCase;
use Psl\Exception\InvariantViolationException;
use Psl\Fun;
use Psl\Vec;

final class ReproduceTest extends TestCase
{
    public function testReproduce(): void
    {
        static::assertSame([1], (Vec\reproduce(1, Fun\identity())));
        static::assertSame([1, 2, 3], Vec\reproduce(3, Fun\identity()));
    }

    public function testThrowsIfNumberIsLowerThan1(): void
    {
        $this->expectException(InvariantViolationException::class);
        $this->expectExceptionMessage('The number of times you want to reproduce must be at least 1.');

        Vec\reproduce(0, Fun\identity());
    }
}
