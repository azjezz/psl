<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Type;

final class IsIntTest extends TestCase
{
    public function testIsInt(): void
    {
        static::assertTrue(Type\is_int(123));
        static::assertTrue(Type\is_int(0));
        static::assertTrue(Type\is_int(Math\INT16_MAX));

        static::assertFalse(Type\is_int('5'));
        static::assertFalse(Type\is_int(5.0));
        static::assertFalse(Type\is_int(true));
        static::assertFalse(Type\is_int(null));
    }
}
