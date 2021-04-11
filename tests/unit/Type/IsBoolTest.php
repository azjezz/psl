<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Type;

final class IsBoolTest extends TestCase
{
    public function testIsBool(): void
    {
        static::assertTrue(Type\is_bool(true));

        static::assertFalse(Type\is_bool(123));
        static::assertFalse(Type\is_bool(0));
        static::assertFalse(Type\is_bool(Math\INT16_MAX));
        static::assertFalse(Type\is_bool('5'));
        static::assertFalse(Type\is_bool(null));
        static::assertFalse(Type\is_bool(5.0));
    }
}
