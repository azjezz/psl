<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use PHPUnit\Framework\TestCase;
use Psl\Str;
use Psl\Type;

class IsStringTest extends TestCase
{
    public function testIsEmpty(): void
    {
        self::assertTrue(Type\is_string(''));
        self::assertTrue(Type\is_string(Str\chr(0)));

        self::assertFalse(Type\is_string(5));
        self::assertFalse(Type\is_string(5.0));
        self::assertFalse(Type\is_string(true));
        self::assertFalse(Type\is_string(null));
        self::assertFalse(Type\is_string(
            new class {
                public function __toString(): string
                {
                    return 'hello';
                }
            }
        ));
    }
}
