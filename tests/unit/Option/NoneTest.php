<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Option;

use PHPUnit\Framework\TestCase;
use Psl\Comparison\Comparable;
use Psl\Comparison\Equable;
use Psl\Comparison\Order;
use Psl\Option;
use Psl\Option\Exception\NoneException;
use Psl\Ref;
use Psl\Str;

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

    public function testOrElse(): void
    {
        static::assertFalse(Option\none()->orElse(static fn() => Option\none())->isSome());
        static::assertTrue(Option\none()->orElse(static fn() => Option\some(4))->isSome());
        static::assertTrue(Option\none()->orElse(static fn() => Option\none())->isNone());
        static::assertFalse(Option\none()->orElse(static fn() => Option\some(4))->isNone());
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

    public function testProceed(): void
    {
        $result = Option\none()->proceed(
            static fn($i) => Str\format('Value is %d', $i),
            static fn() => 'There is no value',
        );

        static::assertSame('There is no value', $result);
    }

    public function testApply(): void
    {
        $spy = new Ref(1);

        $option = Option\none();
        $actual = $option->apply(static function (int $value) use ($spy) {
            $spy->value += $value;
        });

        static::assertSame(1, $spy->value);
        static::assertSame($option, $actual);
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

    public function testComparable(): void
    {
        $a = Option\none();

        static::assertInstanceOf(Comparable::class, $a);
        static::assertSame(Order::Equal, $a->compare(Option\none()));
        static::assertSame(Order::Less, $a->compare(Option\some('some')));
        static::assertSame(Order::Greater, Option\some('some')->compare($a));
    }

    public function testEquality(): void
    {
        $a = Option\none();

        static::assertInstanceOf(Equable::class, $a);
        static::assertTrue($a->equals(Option\none()));
        static::assertFalse($a->equals(Option\some('other')));
    }

    public function testZip(): void
    {
        $x = Option\some(1);
        $y = Option\none();

        static::assertTrue($x->zip($y)->isNone());
        static::assertTrue($y->zip($x)->isNone());
    }

    public function testZipWith(): void
    {
        $x = Option\some(1);
        $y = Option\none();

        static::assertTrue($x->zipWith($y, static fn($a, $b) => $a + $b)->isNone());
        static::assertTrue($y->zipWith($x, static fn($a, $b) => $a + $b)->isNone());
    }

    /**
     * @dataProvider provideTestUnzip
     */
    public function testUnzip(Option\Option $option): void
    {
        [$x, $y] = $option->unzip();

        static::assertTrue($x->isNone());
        static::assertTrue($y->isNone());
    }

    private function provideTestUnzip(): iterable
    {
        yield [Option\none()];
        yield [Option\none()->zip(Option\none())];
        yield [Option\none()->zip(Option\some(1))];
        yield [Option\some(1)->zip(Option\none())];
    }
}
