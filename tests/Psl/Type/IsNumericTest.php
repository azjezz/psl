<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Type;

class IsNumericTest extends TestCase
{
    public function testIsNumeric(): void
    {
        self::assertTrue(Type\is_numeric(Math\NaN));
        self::assertTrue(Type\is_numeric(1));
        self::assertTrue(Type\is_numeric(1.0));
        self::assertTrue(Type\is_numeric('1'));
        self::assertTrue(Type\is_numeric('1.0'));
        self::assertTrue(Type\is_numeric(Math\INFINITY));
        self::assertTrue(Type\is_numeric(5.0));

        self::assertFalse(Type\is_numeric([]));
        self::assertFalse(Type\is_numeric(new \stdClass()));
        self::assertFalse(Type\is_numeric('hello'));
    }
}
