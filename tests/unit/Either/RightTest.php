<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Either;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psl\Comparison\Comparable;
use Psl\Comparison\Equable;
use Psl\Comparison\Order;
use Psl\Either;
use Psl\Either\Left;
use Psl\Either\Right;
use Psl\Ref;

#[CoversClass(Right::class)]
final class RightTest extends TestCase
{
    public function testIsRight(): void
    {
        $either = new Right(42);

        static::assertTrue($either->isRight());
        static::assertFalse($either->isLeft());
    }

    public function testGetRight(): void
    {
        $either = new Right(42);

        static::assertSame(42, $either->getRight());
    }

    public function testGetLeft(): void
    {
        $either = new Right(42);

        $this->expectException(Either\Exception\RightException::class);
        $this->expectExceptionMessage('Attempting to get a left value from a right either.');

        $either->getLeft();
    }

    public function testGetRightOr(): void
    {
        $either = new Right(42);

        static::assertSame(42, $either->getRightOr(0));
    }

    public function testGetLeftOr(): void
    {
        $either = new Right(42);

        static::assertSame('default', $either->getLeftOr('default'));
    }

    public function testGetRightOrElse(): void
    {
        $either = new Right(42);

        static::assertSame(42, $either->getRightOrElse(static fn($v) => 0));
    }

    public function testGetLeftOrElse(): void
    {
        $either = new Right(42);

        static::assertSame('computed from 42', $either->getLeftOrElse(static fn($v) => 'computed from ' . $v));
    }

    public function testUnwrapRight(): void
    {
        $either = new Right(42);

        $option = $either->unwrapRight();

        static::assertTrue($option->isSome());
        static::assertSame(42, $option->unwrap());
    }

    public function testUnwrapLeft(): void
    {
        $either = new Right(42);

        $option = $either->unwrapLeft();

        static::assertTrue($option->isNone());
    }

    public function testMap(): void
    {
        $either = new Right(42);

        $mapped = $either->map(static fn($v) => $v * 2);

        static::assertInstanceOf(Right::class, $mapped);
        static::assertSame(84, $mapped->getRight());
    }

    public function testMapRight(): void
    {
        $either = new Right(42);

        $mapped = $either->mapRight(static fn($v) => $v * 2);

        static::assertInstanceOf(Right::class, $mapped);
        static::assertSame(84, $mapped->getRight());
    }

    public function testMapLeft(): void
    {
        $either = new Right(42);

        $mapped = $either->mapLeft(static fn($v) => 'should not happen');

        static::assertSame($either, $mapped);
        static::assertSame(42, $mapped->getRight());
    }

    public function testFlatMap(): void
    {
        $either = new Right(42);

        $result = $either->flatMap(static fn($v) => new Right($v * 2));

        static::assertInstanceOf(Right::class, $result);
        static::assertSame(84, $result->getRight());
    }

    public function testFlatMapRight(): void
    {
        $either = new Right(42);

        $result = $either->flatMapRight(static fn($v) => new Left('error'));

        static::assertInstanceOf(Left::class, $result);
        static::assertSame('error', $result->getLeft());
    }

    public function testFlatMapLeft(): void
    {
        $either = new Right(42);

        $result = $either->flatMapLeft(static fn($v) => new Right('should not happen'));

        static::assertSame($either, $result);
        static::assertSame(42, $result->getRight());
    }

    public function testProceed(): void
    {
        $result = (new Right(42))->proceed(static fn($v) => 'right: ' . $v, static fn($v) => 'left: ' . $v);

        static::assertSame('right: 42', $result);
    }

    public function testApply(): void
    {
        $spy = new Ref(0);

        $either = new Right(42);
        $actual = $either->apply(static function (int $value) use ($spy) {
            $spy->value = $value;
        });

        static::assertSame(42, $spy->value);
        static::assertSame($actual, $either);
    }

    public function testSwap(): void
    {
        $either = new Right(42);
        $swapped = $either->swap();

        static::assertInstanceOf(Left::class, $swapped);
        static::assertSame(42, $swapped->getLeft());
    }

    public function testContainsRight(): void
    {
        $either = new Right(42);

        static::assertTrue($either->containsRight(42));
        static::assertFalse($either->containsRight(0));
    }

    public function testContainsLeft(): void
    {
        $either = new Right(42);

        static::assertFalse($either->containsLeft(42));
        static::assertFalse($either->containsLeft(0));
    }

    public function testComparable(): void
    {
        $a = new Right(2);

        static::assertInstanceOf(Comparable::class, $a);
        static::assertSame(Order::Equal, $a->compare(new Right(2)));
        static::assertSame(Order::Less, $a->compare(new Right(3)));
        static::assertSame(Order::Greater, $a->compare(new Right(1)));
        static::assertSame(Order::Greater, $a->compare(new Left(1)));
    }

    public function testEquality(): void
    {
        $a = new Right('a');

        static::assertInstanceOf(Equable::class, $a);
        static::assertTrue($a->equals(new Right('a')));
        static::assertFalse($a->equals(new Right('b')));
        static::assertFalse($a->equals(new Left('a')));
    }
}
