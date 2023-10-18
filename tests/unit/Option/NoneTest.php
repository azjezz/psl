<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Option;

use PHPUnit\Framework\TestCase;
use Psl\Option;
use Psl\Option\Exception\NoneException;

final class NoneTest extends TestCase
{
    public function testIsNone(): void
    {
        $option = Option\none();

        static::assertTrue($option->isNone());
        static::assertFalse($option->isSome());
    }

    public function testIsSomeAnd(): void
    {
        $option = Option\none();

        static::assertFalse($option->isSomeAnd(static fn($i) => $i < 10));
        static::assertFalse($option->isSomeAnd(static fn($i) => $i > 10));
    }

    public function testUnwrap(): void
    {
        $option = Option\none();

        $this->expectException(NoneException::class);
        $this->expectExceptionMessage('Attempting to unwrap a none option.');

        $option->unwrap();
    }

    public function testUnwrapOr(): void
    {
        $option = Option\none();

        static::assertSame(4, $option->unwrapOr(4));
    }

    public function testUnwrapOrElse(): void
    {
        $option = Option\none();

        static::assertSame(4, $option->unwrapOrElse(static fn() => 4));
    }

    public function testAnd(): void
    {
        static::assertFalse(Option\none()->and(Option\none())->isSome());
        static::assertFalse(Option\none()->and(Option\some(4))->isSome());
        static::assertTrue(Option\none()->and(Option\none())->isNone());
        static::assertTrue(Option\none()->and(Option\some(4))->isNone());
    }

    public function testOr(): void
    {
        static::assertFalse(Option\none()->or(Option\none())->isSome());
        static::assertTrue(Option\none()->or(Option\some(4))->isSome());
        static::assertTrue(Option\none()->or(Option\none())->isNone());
        static::assertFalse(Option\none()->or(Option\some(4))->isNone());
    }

    public function testFilter(): void
    {
        $option = Option\none();

        static::assertTrue($option->filter(static fn($_) => true)->isNone());
        static::assertTrue($option->filter(static fn($_) => false)->isNone());
    }

    public function testContains(): void
    {
        $option = Option\none();

        static::assertFalse($option->contains(4));
    }

    public function testMap(): void
    {
        $option = Option\none();

        static::assertNull($option->map(static fn($i) => $i + 1)->unwrapOr(null));
    }

    public function testMapOr(): void
    {
        $option = Option\none();

        static::assertSame(4, $option->mapOr(static fn($i) => $i + 1, 4)->unwrap());
    }

    public function testMapOrElse(): void
    {
        $option = Option\none();

        static::assertSame(4, $option->mapOrElse(static fn($i) => $i + 1, static fn() => 4)->unwrap());
    }

    public function testAndThen(): void
    {
        $option = Option\none();

        static::assertNull($option->andThen(static fn($i) => Option\some($i + 1))->unwrapOr(null));
    }

    /**
     * @param Option\Option<int> $value1
     * @param Option\Option<int> $value2
     *
     * @dataProvider provideMerge
     */
    public function testMerge(Option\Option $value1, Option\Option $value2): void
    {
        $this->expectException(NoneException::class);
        $this->expectExceptionMessage('Attempting to unwrap a none option.');

        $value1->merge($value2, static fn($a, $b) => $a + $b)->unwrap();
    }

    public function provideMerge(): iterable
    {
        yield [Option\none(), Option\none()];
        yield [Option\none(), Option\some(2)];
        yield [Option\some(1), Option\none()];
    }
}
