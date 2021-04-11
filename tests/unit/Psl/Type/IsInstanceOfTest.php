<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use PHPUnit\Framework\TestCase;
use Psl\Type;
use stdClass;

final class IsInstanceOfTest extends TestCase
{
    public function testIsInstanceOf(): void
    {
        static::assertTrue(Type\is_instanceof(new stdClass(), stdClass::class));
        static::assertTrue(Type\is_instanceof($this, TestCase::class));

        static::assertFalse(Type\is_instanceof(new stdClass(), TestCase::class));
    }
}
