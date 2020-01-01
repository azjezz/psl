<?php

declare(strict_types=1);

namespace Psl\Tests\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

class IsEmptyTest extends TestCase
{
    public function testIsEmpty(): void
    {
        self::assertTrue(Str\is_empty(''));
        self::assertFalse(Str\is_empty(Str\chr(0)));
        self::assertFalse(Str\is_empty('hello'));
    }
}
