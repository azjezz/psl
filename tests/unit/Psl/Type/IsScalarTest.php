<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Str;
use Psl\Type;

final class IsScalarTest extends TestCase
{
    public function testIsScalar(): void
    {
        static::assertTrue(Type\is_scalar(''));
        static::assertTrue(Type\is_scalar(Str\chr(0)));
        static::assertTrue(Type\is_scalar(123));
        static::assertTrue(Type\is_scalar(0));
        static::assertTrue(Type\is_scalar(Math\INT16_MAX));
        static::assertTrue(Type\is_scalar(Math\INFINITY));
        static::assertTrue(Type\is_scalar(5.0));
        static::assertTrue(Type\is_scalar(true));

        static::assertFalse(Type\is_scalar(null));
        static::assertFalse(Type\is_scalar(
            new class {
                public function __toString(): string
                {
                    return 'hello';
                }
            }
        ));
    }
}
