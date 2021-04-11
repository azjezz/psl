<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Type;

final class IsFloatTest extends TestCase
{
    public function testIsFloat(): void
    {
        static::assertTrue(Type\is_float(5.0));

        static::assertFalse(Type\is_float(123));
        static::assertFalse(Type\is_float(0));
        static::assertFalse(Type\is_float(Math\INT16_MAX));
        static::assertFalse(Type\is_float('5'));
        static::assertFalse(Type\is_float(true));
        static::assertFalse(Type\is_float(null));
    }
}
