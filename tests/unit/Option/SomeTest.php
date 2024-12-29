<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Option;

use PHPUnit\Framework\TestCase;
use Psl\Comparison\Comparable;
use Psl\Comparison\Equable;
use Psl\Comparison\Order;
use Psl\Option;
use Psl\Ref;
use Psl\Str;
use Psl\Tests\Fixture;

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

    public function testOrElse(): void
    {
        static::assertTrue(Option\some(2)->orElse(static fn() => Option\none())->isSome());
        static::assertTrue(Option\some(2)->orElse(static fn() => Option\some(4))->isSome());
        static::assertFalse(Option\some(2)->orElse(static fn() => Option\none())->isNone());
        static::assertFalse(Option\some(2)->orElse(static fn() => Option\some(4))->isNone());
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

    public function testProceed(): void
    {
        $result = Option\some(1)->proceed(
            static fn($i) => Str\format('Value is %d', $i),
            static fn() => 'There is no value',
        );

        static::assertSame('Value is 1', $result);
    }

    public function testApply(): void
    {
        $spy = new Ref(1);

        $option = Option\some(2);
        $actual = $option->apply(static function (int $value) use ($spy) {
            $spy->value += $value;
        });

        static::assertSame(3, $spy->value);
        static::assertSame($actual, $option);
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

    public function testAndThen(): void
    {
        $option = Option\some(2);

        static::assertSame(3, $option->andThen(static fn($i) => Option\some($i + 1))->unwrapOr(null));
    }

    public function testComparable(): void
    {
        $a = Option\some(2);

        static::assertInstanceOf(Comparable::class, $a);
        static::assertSame(Order::Equal, $a->compare(Option\some(2)));
        static::assertSame(Order::Less, Option\none()->compare(Option\some(1)));
        static::assertSame(Order::Greater, $a->compare(Option\none()));
        static::assertSame(Order::Less, $a->compare(Option\some(3)));
    }

    public function testEquality()
    {
        $a = Option\some('a');

        static::assertInstanceOf(Equable::class, $a);
        static::assertFalse($a->equals(Option\none()));
        static::assertFalse($a->equals(Option\some('other')));
        static::assertTrue($a->equals(Option\some('a')));
    }

    public function testZip(): void
    {
        $x = Option\some(1);
        $y = Option\some('hi');

        static::assertTrue(Option\some([1, 'hi'])->equals($x->zip($y)));
        static::assertTrue(Option\some(['hi', 1])->equals($y->zip($x)));
    }

    public function testZipWith(): void
    {
        $x = Option\some(17);
        $y = Option\some(42);

        $point = $x->zipWith($y, static fn($a, $b) => new Fixture\Point($a, $b));

        static::assertTrue(Option\some(new Fixture\Point(17, 42))->equals($point));
    }

    /**
     * @dataProvider provideTestUnzip
     */
    public function testUnzip(Option\Option $option, mixed $expectedX, mixed $expectedY): void
    {
        [$x, $y] = $option->unzip();

        static::assertSame($expectedX, $x->unwrap());
        static::assertSame($expectedY, $y->unwrap());
    }

    private function provideTestUnzip(): iterable
    {
        yield [Option\some(null)->zip(Option\some('hi')), null, 'hi'];
        yield [Option\some(1)->zip(Option\some('hi')), 1, 'hi'];
        yield [Option\some([true, false]), true, false];
    }

    private function provideTestUnzipAssertionException(): iterable
    {
        yield [Option\some(null)];
        yield [Option\some(1)];
        yield [Option\some([true])];
    }
}
