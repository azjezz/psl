<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Str;

use PHPUnit\Framework\TestCase;
use Psl\Str;

final class IsEmptyTest extends TestCase
{
    public function testIsEmpty(): void
    {
        static::assertTrue(Str\is_empty(''));
        static::assertFalse(Str\is_empty(Str\chr(0)));
        static::assertFalse(Str\is_empty('hello'));
    }
}
