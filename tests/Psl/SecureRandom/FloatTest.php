<?php

declare(strict_types=1);

namespace Psl\Tests\SecureRandom;

use PHPUnit\Framework\TestCase;
use Psl\SecureRandom;

final class FloatTest extends TestCase
{
    public function testFloat(): void
    {
        $random = SecureRandom\float();

        static::assertIsFloat($random);
        static::assertGreaterThanOrEqual(0, $random);
        static::assertLessThanOrEqual(1, $random);
    }
}
