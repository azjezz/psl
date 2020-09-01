<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Type;

class IsNaNTest extends TestCase
{
    public function testIsNaN(): void
    {
        self::assertTrue(Type\is_nan(Math\NaN));

        self::assertFalse(Type\is_nan(Math\INFINITY));
        self::assertFalse(Type\is_nan(5.0));
    }
}
