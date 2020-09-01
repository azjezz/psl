<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Type;

class IsIntTest extends TestCase
{
    public function testIsInt(): void
    {
        self::assertTrue(Type\is_int(123));
        self::assertTrue(Type\is_int(0));
        self::assertTrue(Type\is_int(Math\INT16_MAX));

        self::assertFalse(Type\is_int('5'));
        self::assertFalse(Type\is_int(5.0));
        self::assertFalse(Type\is_int(true));
        self::assertFalse(Type\is_int(null));
    }
}
