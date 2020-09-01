<?php

declare(strict_types=1);

namespace Psl\Tests\Type;

use PHPUnit\Framework\TestCase;
use Psl\Type;
use stdClass;

class IsInstanceOfTest extends TestCase
{
    public function testIsInstanceOf(): void
    {
        self::assertTrue(Type\is_instanceof(new stdClass(), stdClass::class));
        self::assertTrue(Type\is_instanceof($this, TestCase::class));

        self::assertFalse(Type\is_instanceof(new stdClass(), TestCase::class));
    }
}
