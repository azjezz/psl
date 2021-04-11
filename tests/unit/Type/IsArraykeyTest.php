<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Str;
use Psl\Type;

final class IsArraykeyTest extends TestCase
{
    public function testIsArraykey(): void
    {
        static::assertTrue(Type\is_arraykey(''));
        static::assertTrue(Type\is_arraykey(Str\chr(0)));
        static::assertTrue(Type\is_arraykey(123));
        static::assertTrue(Type\is_arraykey(0));
        static::assertTrue(Type\is_arraykey(Math\INT16_MAX));

        static::assertFalse(Type\is_arraykey(5.0));
        static::assertFalse(Type\is_arraykey(true));
        static::assertFalse(Type\is_arraykey(null));
        static::assertFalse(Type\is_arraykey(
            new class {
                public function __toString(): string
                {
                    return 'hello';
                }
            }
        ));
    }
}
