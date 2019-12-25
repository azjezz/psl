<?php

declare(strict_types=1);

namespace Psl\Tests\Random;

use PHPUnit\Framework\TestCase;
use Psl\Exception;
use Psl\Math;
use Psl\Random;

/**
 * @covers \Psl\Random\int
 */
class IntTest extends TestCase
{
    public function testInt(): void
    {
        $random = Random\int();

        self::assertIsInt($random);
        self::assertGreaterThanOrEqual(Math\INT64_MIN, $random);
        self::assertLessThanOrEqual(Math\INT64_MAX, $random);
    }

    public function testIntWithASpecificMin(): void
    {
        $random = Random\int(10);

        self::assertIsInt($random);
        self::assertGreaterThanOrEqual(10, $random);
        self::assertLessThanOrEqual(Math\INT64_MAX, $random);
    }

    public function testIntWithASpecificRange(): void
    {
        $random = Random\int(20, 1200);

        self::assertIsInt($random);
        self::assertGreaterThanOrEqual(20, $random);
        self::assertLessThanOrEqual(1200, $random);
    }

    public function testIntWithMinGreaterThanMax(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Expected $min (20) to be less than or equal to $max (5).');

        Random\int(20, 5);
    }
}
