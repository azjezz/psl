<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Str;
use Psl\Type;

class IsArraykeyTest extends TestCase
{
    public function testIsArraykey(): void
    {
        self::assertTrue(Type\is_arraykey(''));
        self::assertTrue(Type\is_arraykey(Str\chr(0)));
        self::assertTrue(Type\is_arraykey(123));
        self::assertTrue(Type\is_arraykey(0));
        self::assertTrue(Type\is_arraykey(Math\INT16_MAX));

        self::assertFalse(Type\is_arraykey(5.0));
        self::assertFalse(Type\is_arraykey(true));
        self::assertFalse(Type\is_arraykey(null));
        self::assertFalse(Type\is_arraykey(
            new class {
                public function __toString(): string
                {
                    return 'hello';
                }
            }
        ));
    }
}
