<?php

declare(strict_types=1);

namespace Psl\Option\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psl\Comparison\Comparable;
use Psl\Comparison\Equable;
use Psl\Comparison\Order;
use Psl\Option;
use Psl\Option\Exception\NoneException;
use Psl\Ref;

use function sprintf;

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

        static::assertFalse($option->isSomeAnd(static fn(int $i): int => $i < 10));
        static::assertFalse($option->isSomeAnd(static fn(int $i): int => $i > 10));
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

        static::assertSame(4, $option->unwrapOr::<int>(4));
    }

    public function testUnwrapOrElse(): void
    {
        $option = Option\none();

        static::assertSame(4, $option->unwrapOrElse::<int>(static fn(): int => 4));
    }

    public function testAnd(): void
    {
        static::assertFalse(Option\none()->and::<int>(Option\none())->isSome());
        static::assertFalse(Option\none()->and::<int>(Option\some::<int>(4))->isSome());
        static::assertTrue(Option\none()->and::<int>(Option\none())->isNone());
        static::assertTrue(Option\none()->and::<int>(Option\some::<int>(4))->isNone());
    }

    public function testOr(): void
    {
        static::assertFalse(Option\none()->or::<int>(Option\none())->isSome());
        static::assertTrue(Option\none()->or::<int>(Option\some::<int>(4))->isSome());
        static::assertTrue(Option\none()->or::<int>(Option\none())->isNone());
        static::assertFalse(Option\none()->or::<int>(Option\some::<int>(4))->isNone());
    }

    public function testOrElse(): void
    {
        static::assertFalse(Option\none()->orElse::<int>(Option\none(...))->isSome());
        static::assertTrue(Option\none()->orElse::<int>(static fn(): Option\Option => Option\some::<int>(4))->isSome());
        static::assertTrue(Option\none()->orElse::<int>(Option\none(...))->isNone());
        static::assertFalse(Option\none()->orElse::<int>(static fn(): Option\Option => Option\some::<int>(4))->isNone());
    }

    public function testFilter(): void
    {
        $option = Option\none();

        static::assertTrue($option->filter(static fn(mixed $_): bool => true)->isNone());
        static::assertTrue($option->filter(static fn(mixed $_): bool => false)->isNone());
    }

    public function testContains(): void
    {
        $option = Option\none();

        static::assertFalse($option->contains(4));
    }

    public function testProceed(): void
    {
        $result = Option\none()->proceed::<string>(
            static fn(int $i): string => sprintf('Value is %d', $i),
            static fn(): string => 'There is no value',
        );

        static::assertSame('There is no value', $result);
    }

    public function testApply(): void
    {
        $spy = new Ref::<int>(1);

        $option = Option\none();
        $actual = $option->apply(static function (int $value) use ($spy): void {
            $spy->value += $value;
        });

        static::assertSame(1, $spy->value);
        static::assertSame($option, $actual);
    }

    public function testMap(): void
    {
        $option = Option\none();

        static::assertNull($option->map::<int>(static fn(int $i): int => $i + 1)->unwrapOr::<null>(null));
    }

    public function testMapOr(): void
    {
        $option = Option\none();

        static::assertSame(4, $option->mapOr::<int>(static fn(int $i): int => $i + 1, 4)->unwrap());
    }

    public function testMapOrElse(): void
    {
        $option = Option\none();

        static::assertSame(4, $option->mapOrElse::<int>(static fn(int $i): int => $i + 1, static fn(): int => 4)->unwrap());
    }

    public function testAndThen(): void
    {
        $option = Option\none();

        static::assertNull($option->andThen::<int>(static fn(int $i): int => Option\some::<int>($i + 1))->unwrapOr::<null>(null));
    }

    public function testComparable(): void
    {
        $a = Option\none();

        static::assertInstanceOf(Comparable::class, $a);
        static::assertSame(Order::Equal, $a->compare(Option\none()));
        static::assertSame(Order::Less, $a->compare(Option\some::<string>('some')));
        static::assertSame(Order::Greater, Option\some::<string>('some')->compare($a));
    }

    public function testEquality(): void
    {
        $a = Option\none();

        static::assertInstanceOf(Equable::class, $a);
        static::assertTrue($a->equals(Option\none()));
        static::assertFalse($a->equals(Option\some::<string>('other')));
    }

    public function testZip(): void
    {
        $x = Option\some::<int>(1);
        $y = Option\none();

        static::assertTrue($x->zip::<never>($y)->isNone());
        static::assertTrue($y->zip::<int>($x)->isNone());
    }

    public function testZipWith(): void
    {
        $x = Option\some::<int>(1);
        $y = Option\none();

        static::assertTrue($x->zipWith::<never, int>($y, static fn(int $a, int $b): int => $a + $b)->isNone());
        static::assertTrue($y->zipWith::<int, int>($x, static fn(int $a, int $b): int => $a + $b)->isNone());
    }

    /**
     * @param Option\Option<array{mixed, mixed}> $option
     */
    #[DataProvider('provideTestUnzip')]
    public function testUnzip(Option\Option<array> $option): void
    {
        [$x, $y] = $option->unzip::<mixed, mixed>();

        static::assertTrue($x->isNone());
        static::assertTrue($y->isNone());
    }

    public static function provideTestUnzip(): iterable
    {
        yield [Option\none()];
        yield [Option\none()->zip::<never>(Option\none())];
        yield [Option\none()->zip::<int>(Option\some::<int>(1))];
        yield [Option\some::<int>(1)->zip::<never>(Option\none())];
    }
}
