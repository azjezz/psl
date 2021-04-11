<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Type;

use PHPUnit\Framework\TestCase;
use Psl\Math;
use Psl\Type;

use const STDOUT;

final class IsResourceTest extends TestCase
{
    public function testIsResource(): void
    {
        static::assertTrue(Type\is_resource(STDOUT));

        static::assertFalse(Type\is_resource(null));
        static::assertFalse(Type\is_resource(123));
        static::assertFalse(Type\is_resource(0));
        static::assertFalse(Type\is_resource(Math\INT16_MAX));
        static::assertFalse(Type\is_resource('5'));
        static::assertFalse(Type\is_resource(true));
        static::assertFalse(Type\is_resource(5.0));
    }
}
