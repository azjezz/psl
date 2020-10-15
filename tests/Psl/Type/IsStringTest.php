<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use PHPUnit\Framework\TestCase;
use Psl\Str;
use Psl\Type;

final class IsStringTest extends TestCase
{
    public function testIsString(): void
    {
        static::assertTrue(Type\is_string(''));
        static::assertTrue(Type\is_string(Str\chr(0)));

        static::assertFalse(Type\is_string(5));
        static::assertFalse(Type\is_string(5.0));
        static::assertFalse(Type\is_string(true));
        static::assertFalse(Type\is_string(null));
        static::assertFalse(Type\is_string(
            new class {
                public function __toString(): string
                {
                    return 'hello';
                }
            }
        ));
    }
}
