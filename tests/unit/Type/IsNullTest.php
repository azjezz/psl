<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Type;

final class IsNullTest extends TestCase
{
    public function testIsNull(): void
    {
        static::assertTrue(Type\is_null(null));

        static::assertFalse(Type\is_null(123));
        static::assertFalse(Type\is_null(0));
        static::assertFalse(Type\is_null(Math\INT16_MAX));
        static::assertFalse(Type\is_null('5'));
        static::assertFalse(Type\is_null(true));
        static::assertFalse(Type\is_null(5.0));
    }
}
