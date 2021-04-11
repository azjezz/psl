<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Type;

final class IsNaNTest extends TestCase
{
    public function testIsNaN(): void
    {
        static::assertTrue(Type\is_nan(Math\NAN));

        static::assertFalse(Type\is_nan(Math\INFINITY));
        static::assertFalse(Type\is_nan(5.0));
    }
}
