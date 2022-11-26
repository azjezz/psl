<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Option;

use PHPUnit\Framework\TestCase;
use Psl\Option;

final class SomeTest extends TestCase
{
    public function testIsSome(): void
    {
        $option = Option\some(4);

        static::assertFalse($option->isNone());
        static::assertTrue($option->isSome());
    }

    public function testIsSomeAnd(): void
    {
        $option = Option\some(4);

        static::assertTrue($option->isSomeAnd(static fn($i) => $i < 10));
        static::assertFalse($option->isSomeAnd(static fn($i) => $i > 10));
    }

    public function testUnwrap(): void
    {
        $option = Option\some(4);

        static::assertSame(4, $option->unwrap());
    }

    public function testUnwrapOr(): void
    {
        $option = Option\some(2);

        static::assertSame(2, $option->unwrapOr(4));
    }

    public function testUnwrapOrElse(): void
    {
        $option = Option\some(2);

        static::assertSame(2, $option->unwrapOrElse(static fn() => 4));
    }

    public function testAnd(): void
    {
        static::assertFalse(Option\some(2)->and(Option\none())->isSome());
        static::assertTrue(Option\some(2)->and(Option\some(4))->isSome());
        static::assertTrue(Option\some(2)->and(Option\none())->isNone());
        static::assertFalse(Option\some(2)->and(Option\some(4))->isNone());
    }

    public function testOr(): void
    {
        static::assertTrue(Option\some(2)->or(Option\none())->isSome());
        static::assertTrue(Option\some(2)->or(Option\some(4))->isSome());
        static::assertFalse(Option\some(2)->or(Option\none())->isNone());
        static::assertFalse(Option\some(2)->or(Option\some(4))->isNone());
    }

    public function testFilter(): void
    {
        $option = Option\some(2);

        static::assertTrue($option->filter(static fn($_) => true)->isSome());
        static::assertTrue($option->filter(static fn($_) => false)->isNone());
    }

    public function testContains(): void
    {
        $option = Option\some(2);

        static::assertFalse($option->contains(4));
        static::assertTrue($option->contains(2));
    }

    public function testMap(): void
    {
        $option = Option\some(2);

        static::assertSame(3, $option->map(static fn($i) => $i + 1)->unwrapOr(0));
    }

    public function testMapOr(): void
    {
        $option = Option\some(2);

        static::assertSame(3, $option->mapOr(static fn($i) => $i + 1, 4)->unwrap());
    }

    public function testMapOrElse(): void
    {
        $option = Option\some(2);

        static::assertSame(3, $option->mapOrElse(static fn($i) => $i + 1, static fn() => 4)->unwrap());
    }
}
