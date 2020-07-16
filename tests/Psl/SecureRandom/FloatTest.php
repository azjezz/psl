<?php

declare(strict_types=1);

namespace Psl\Tests\SecureRandom;

use PHPUnit\Framework\TestCase;
use Psl\SecureRandom;

class FloatTest extends TestCase
{
    public function testFloat(): void
    {
        $random = SecureRandom\float();

        self::assertIsFloat($random);
        self::assertGreaterThanOrEqual(0, $random);
        self::assertLessThanOrEqual(1, $random);
    }
}
