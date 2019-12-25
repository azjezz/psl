<?php

declare(strict_types=1);

namespace Psl\Tests\Random;

use PHPUnit\Framework\TestCase;
use Psl\Random;

/**
 * @covers \Psl\Random\float
 */
class FloatTest extends TestCase
{
    public function testFloat(): void
    {
        $random = Random\float();

        self::assertIsFloat($random);
        self::assertGreaterThanOrEqual(0, $random);
        self::assertLessThanOrEqual(1, $random);
    }
}
