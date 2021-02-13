<?php

declare(strict_types=1);

namespace Psl\Tests\Vec;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\Vec;

final class FillTest extends TestCase
{
    public function testRepeat(): void
    {
        $result = Vec\fill(5, 42);

        static::assertSame([42, 42, 42, 42, 42], $result);
    }

    public function testRepeatThrowsIfNumIsNegative(): void
    {
        $this->expectException(Psl\Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Expected non-negative fill size, got -1.');

        Vec\fill(-1, 4);
    }
}
