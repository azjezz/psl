<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\PseudoRandom;

use PHPUnit\Framework\TestCase;
use Psl\PseudoRandom;

final class FloatTest extends TestCase
{
    public function testFloat(): void
    {
        $random = PseudoRandom\float();

        static::assertIsFloat($random);
        static::assertGreaterThanOrEqual(0, $random);
        static::assertLessThanOrEqual(1, $random);
    }
}
