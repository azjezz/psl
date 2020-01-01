<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

class IsStringTest extends TestCase
{
    public function testIsEmpty(): void
    {
        self::assertTrue(Str\is_string(''));
        self::assertTrue(Str\is_string(Str\chr(0)));

        self::assertFalse(Str\is_string(5));
        self::assertFalse(Str\is_string(5.0));
        self::assertFalse(Str\is_string(true));
        self::assertFalse(Str\is_string(null));
        self::assertFalse(Str\is_string(
            new class {
                public function __toString(): string
                {
                    return 'hello';
                }
            }
        ));
    }
}
