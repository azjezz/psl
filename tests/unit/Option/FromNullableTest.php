<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Option;

use PHPUnit\Framework\TestCase;
use Psl\Option;
use stdClass;

final class FromNullableTest extends TestCase
{
    public function testIsSome(): void
    {
        static::assertTrue(Option\from_nullable(1)->isSome());
        static::assertTrue(Option\from_nullable(1.1)->isSome());
        static::assertTrue(Option\from_nullable(true)->isSome());
        static::assertTrue(Option\from_nullable(false)->isSome());
        static::assertTrue(Option\from_nullable('hello')->isSome());
        static::assertTrue(Option\from_nullable([])->isSome());
        static::assertTrue(Option\from_nullable(new stdClass())->isSome());
        static::assertTrue(Option\from_nullable(static fn(): string => '')->isSome());
        static::assertTrue(Option\from_nullable(static function (): iterable {
            yield 'hello';
        })->isSome());
    }

    public function testIsNone(): void
    {
        $option = Option\from_nullable(null);

        static::assertTrue($option->isNone());
    }
}
