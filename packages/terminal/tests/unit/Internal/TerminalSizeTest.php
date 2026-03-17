<?php

declare(strict_types=1);

namespace Psl\Terminal\Tests\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Psl\Terminal\Internal\TerminalSize;

final class TerminalSizeTest extends TestCase
{
    public function testGetReturnsArrayOfTwoIntegers(): void
    {
        $result = TerminalSize::get();

        static::assertCount(2, $result);
        static::assertIsInt($result[0]);
        static::assertIsInt($result[1]);
    }

    public function testGetReturnsPositiveDimensions(): void
    {
        [$cols, $rows] = TerminalSize::get();

        static::assertGreaterThan(0, $cols);
        static::assertGreaterThan(0, $rows);
    }

    public function testGetReturnsConsistentResults(): void
    {
        $first = TerminalSize::get();
        $second = TerminalSize::get();

        static::assertSame($first, $second);
    }

    public function testColumnsAreFirst(): void
    {
        [$cols, $rows] = TerminalSize::get();

        static::assertGreaterThanOrEqual(1, $cols);
        static::assertGreaterThanOrEqual(1, $rows);
    }
}
