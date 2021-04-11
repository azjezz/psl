<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Type;
use stdClass;

final class IsNumericTest extends TestCase
{
    public function testIsNumeric(): void
    {
        static::assertTrue(Type\is_numeric(Math\NAN));
        static::assertTrue(Type\is_numeric(1));
        static::assertTrue(Type\is_numeric(1.0));
        static::assertTrue(Type\is_numeric('1'));
        static::assertTrue(Type\is_numeric('1.0'));
        static::assertTrue(Type\is_numeric(Math\INFINITY));
        static::assertTrue(Type\is_numeric(5.0));

        static::assertFalse(Type\is_numeric([]));
        static::assertFalse(Type\is_numeric(new stdClass()));
        static::assertFalse(Type\is_numeric('hello'));
    }
}
