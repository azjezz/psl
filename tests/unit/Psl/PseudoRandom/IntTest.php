<?php

declare(strict_types=1);

namespace Psl\Tests\PseudoRandom;

use PHPUnit\Framework\TestCase;
use Psl\Exception;
use Psl\Math;
use Psl\PseudoRandom;

final class IntTest extends TestCase
{
    public function testInt(): void
    {
        $random = PseudoRandom\int();

        static::assertIsInt($random);
        static::assertGreaterThanOrEqual(Math\INT64_MIN, $random);
        static::assertLessThanOrEqual(Math\INT64_MAX, $random);
    }

    public function testIntWithASpecificMin(): void
    {
        $random = PseudoRandom\int(10);

        static::assertIsInt($random);
        static::assertGreaterThanOrEqual(10, $random);
        static::assertLessThanOrEqual(Math\INT64_MAX, $random);
    }

    public function testIntWithASpecificRange(): void
    {
        $random = PseudoRandom\int(20, 1200);

        static::assertIsInt($random);
        static::assertGreaterThanOrEqual(20, $random);
        static::assertLessThanOrEqual(1200, $random);
    }

    public function testIntWithMinGreaterThanMax(): void
    {
        $this->expectException(Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Expected $min (20) to be less than or equal to $max (5).');

        PseudoRandom\int(20, 5);
    }
}
