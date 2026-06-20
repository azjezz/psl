<?php

declare(strict_types=1);

namespace Psl\Option\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Comparison\Comparable;
use Psl\Comparison\Equable;
use Psl\Comparison\Order;
use Psl\Option;
use Psl\Option\Tests\Fixture;
use Psl\Ref;

use function sprintf;

final class SomeTest extends TestCase
{
    public function testIsSome(): void
    {
        $option = Option\some::<int>(4);

        static::assertFalse($option->isNone());
        static::assertTrue($option->isSome());
    }

    public function testIsSomeAnd(): void
    {
        $option = Option\some::<int>(4);

        static::assertTrue($option->isSomeAnd(static fn(int $i): bool => $i < 10));
        static::assertFalse($option->isSomeAnd(static fn(int $i): bool => $i > 10));
    }

    public function testUnwrap(): void
    {
        $option = Option\some::<int>(4);

        static::assertSame(4, $option->unwrap());
    }

    public function testUnwrapOr(): void
    {
        $option = Option\some::<int>(2);

        static::assertSame(2, $option->unwrapOr::<int>(4));
    }

    public function testUnwrapOrElse(): void
    {
        $option = Option\some::<int>(2);

        static::assertSame(2, $option->unwrapOrElse::<int>(static fn(): int => 4));
    }

    public function testAnd(): void
    {
        static::assertFalse(Option\some::<int>(2)->and::<int>(Option\none())->isSome());
        static::assertTrue(Option\some::<int>(2)->and::<int>(Option\some::<int>(4))->isSome());
        static::assertTrue(Option\some::<int>(2)->and::<int>(Option\none())->isNone());
        static::assertFalse(Option\some::<int>(2)->and::<int>(Option\some::<int>(4))->isNone());
    }

    public function testOr(): void
    {
        static::assertTrue(Option\some::<int>(2)->or::<int>(Option\none())->isSome());
        static::assertTrue(Option\some::<int>(2)->or::<int>(Option\some::<int>(4))->isSome());
        static::assertFalse(Option\some::<int>(2)->or::<int>(Option\none())->isNone());
        static::assertFalse(Option\some::<int>(2)->or::<int>(Option\some::<int>(4))->isNone());
    }

    public function testOrElse(): void
    {
        static::assertTrue(Option\some::<int>(2)->orElse::<int>(Option\none(...))->isSome());
        static::assertTrue(Option\some::<int>(2)->orElse::<int>(static fn(): Option\Option => Option\some::<int>(4))->isSome());
        static::assertFalse(Option\some::<int>(2)->orElse::<int>(Option\none(...))->isNone());
        static::assertFalse(Option\some::<int>(2)->orElse::<int>(static fn(): Option\Option => Option\some::<int>(4))->isNone());
    }

    public function testFilter(): void
    {
        $option = Option\some::<int>(2);

        static::assertTrue($option->filter(static fn(int $_): bool => true)->isSome());
        static::assertTrue($option->filter(static fn(int $_): bool => false)->isNone());
    }

    public function testContains(): void
    {
        $option = Option\some::<int>(2);

        static::assertFalse($option->contains(4));
        static::assertTrue($option->contains(2));
    }

    public function testProceed(): void
    {
        $result = Option\some::<int>(1)
            ->proceed::<string>(
                static fn(int $i): string => sprintf('Value is %d', $i),
                static fn(): string => 'There is no value',
            );

        static::assertSame('Value is 1', $result);
    }

    public function testApply(): void
    {
        $spy = new Ref::<int>(1);

        $option = Option\some::<int>(2);
        $actual = $option->apply(static function (int $value) use ($spy): void {
            $spy->value += $value;
        });

        static::assertSame(3, $spy->value);
        static::assertSame($actual, $option);
    }

    public function testMap(): void
    {
        $option = Option\some::<int>(2);

        static::assertSame(3, $option->map::<int>(static fn(int $i): int => $i + 1)->unwrapOr::<int>(0));
    }

    public function testMapOr(): void
    {
        $option = Option\some::<int>(2);

        static::assertSame(3, $option->mapOr::<int>(static fn(int $i): int => $i + 1, 4)->unwrap());
    }

    public function testMapOrElse(): void
    {
        $option = Option\some::<int>(2);

        static::assertSame(3, $option->mapOrElse::<int>(static fn(int $i): int => $i + 1, static fn(): int => 4)->unwrap());
    }

    public function testAndThen(): void
    {
        $option = Option\some::<int>(2);

        static::assertSame(
            3,
            $option->andThen::<int>(static fn(int $i): Option\Option => Option\some::<int>($i + 1))->unwrapOr::<null>(null),
        );
    }

    public function testComparable(): void
    {
        $a = Option\some::<int>(2);

        static::assertInstanceOf(Comparable::class, $a);
        static::assertSame(Order::Equal, $a->compare(Option\some::<int>(2)));
        static::assertSame(Order::Less, Option\none()->compare(Option\some::<int>(1)));
        static::assertSame(Order::Greater, $a->compare(Option\none()));
        static::assertSame(Order::Less, $a->compare(Option\some::<int>(3)));
    }

    public function testEquality(): void
    {
        $a = Option\some::<string>('a');

        static::assertInstanceOf(Equable::class, $a);
        static::assertFalse($a->equals(Option\none()));
        static::assertFalse($a->equals(Option\some::<string>('other')));
        static::assertTrue($a->equals(Option\some::<string>('a')));
    }

    public function testZip(): void
    {
        $x = Option\some::<int>(1);
        $y = Option\some::<string>('hi');

        static::assertTrue(Option\some::<array>([1, 'hi'])->equals($x->zip::<string>($y)));
        static::assertTrue(Option\some::<array>(['hi', 1])->equals($y->zip::<int>($x)));
    }

    public function testZipWith(): void
    {
        $x = Option\some::<int>(17);
        $y = Option\some::<int>(42);

        $point = $x->zipWith::<int, Fixture\Point>($y, static fn(int $a, int $b): Fixture\Point => new Fixture\Point($a, $b));

        static::assertTrue(Option\some::<Fixture\Point>(new Fixture\Point(17, 42))->equals($point));
    }

    /**
     * @param Option\Option<array{mixed, mixed}> $option
     */
    #[DataProvider('provideTestUnzip')]
    public function testUnzip(Option\Option<array> $option, mixed $expectedX, mixed $expectedY): void
    {
        [$x, $y] = $option->unzip::<mixed, mixed>();

        static::assertSame($expectedX, $x->unwrap());
        static::assertSame($expectedY, $y->unwrap());
    }

    public static function provideTestUnzip(): iterable
    {
        yield [Option\some::<null>(null)->zip::<string>(Option\some::<string>('hi')), null, 'hi'];
        yield [Option\some::<int>(1)->zip::<string>(Option\some::<string>('hi')), 1, 'hi'];
        yield [Option\some::<array>([true, false]), true, false];
    }

    public static function provideTestUnzipAssertionException(): iterable
    {
        yield [Option\some::<null>(null)];
        yield [Option\some::<int>(1)];
        yield [Option\some::<array>([true])];
    }
}
