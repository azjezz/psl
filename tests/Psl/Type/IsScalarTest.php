<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Str;
use Psl\Type;

class IsScalarTest extends TestCase
{
    public function testIsScalar(): void
    {
        self::assertTrue(Type\is_scalar(''));
        self::assertTrue(Type\is_scalar(Str\chr(0)));
        self::assertTrue(Type\is_scalar(123));
        self::assertTrue(Type\is_scalar(0));
        self::assertTrue(Type\is_scalar(Math\INT16_MAX));
        self::assertTrue(Type\is_scalar(Math\INFINITY));
        self::assertTrue(Type\is_scalar(5.0));
        self::assertTrue(Type\is_scalar(true));

        self::assertFalse(Type\is_scalar(null));
        self::assertFalse(Type\is_scalar(
            new class {
                public function __toString(): string
                {
                    return 'hello';
                }
            }
        ));
    }
}
