<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\SecureRandom;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\SecureRandom;

final class IntTest extends TestCase
{
    public function testInt(): void
    {
        $random = SecureRandom\int();

        static::assertIsInt($random);
        static::assertGreaterThanOrEqual(Math\INT64_MIN, $random);
        static::assertLessThanOrEqual(Math\INT64_MAX, $random);
    }

    public function testIntWithASpecificMin(): void
    {
        $random = SecureRandom\int(10);

        static::assertIsInt($random);
        static::assertGreaterThanOrEqual(10, $random);
        static::assertLessThanOrEqual(Math\INT64_MAX, $random);
    }

    public function testIntWithASpecificRange(): void
    {
        $random = SecureRandom\int(20, 1200);

        static::assertIsInt($random);
        static::assertGreaterThanOrEqual(20, $random);
        static::assertLessThanOrEqual(1200, $random);
    }

    public function testIntWithAnEqualRange(): void
    {
        $random = SecureRandom\int(20, 20);

        static::assertIsInt($random);
        static::assertSame(20, $random);
    }

    public function testIntWithMinGreaterThanMax(): void
    {
        $this->expectException(SecureRandom\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected $min (20) to be less than or equal to $max (5).');

        SecureRandom\int(20, 5);
    }
}
