<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\Either;

use PHPUnit\Framework\TestCase;
use Psl\Comparison\Comparable;
use Psl\Comparison\Equable;
use Psl\Comparison\Order;
use Psl\Either;
use Psl\Either\Left;
use Psl\Either\Right;
use Psl\Ref;
use Psl\Str;

/**
 * @covers \Psl\Either\Left
 */
final class LeftTest extends TestCase
{
    public function testIsLeft(): void
    {
        $either = new Left('error');

        static::assertTrue($either->isLeft());
        static::assertFalse($either->isRight());
    }

    public function testGetLeft(): void
    {
        $either = new Left('error');

        static::assertSame('error', $either->getLeft());
    }

    public function testGetRight(): void
    {
        $either = new Left('error');

        $this->expectException(Either\Exception\LeftException::class);
        $this->expectExceptionMessage('Attempting to get a right value from a left either.');

        $either->getRight();
    }

    public function testGetLeftOr(): void
    {
        $either = new Left('error');

        static::assertSame('error', $either->getLeftOr('default'));
    }

    public function testGetRightOr(): void
    {
        $either = new Left('error');

        static::assertSame('default', $either->getRightOr('default'));
    }

    public function testGetLeftOrElse(): void
    {
        $either = new Left('error');

        static::assertSame('error', $either->getLeftOrElse(static fn($v) => 'computed'));
    }

    public function testGetRightOrElse(): void
    {
        $either = new Left('error');

        static::assertSame('computed from error', $either->getRightOrElse(static fn($v) => 'computed from ' . $v));
    }

    public function testUnwrapLeft(): void
    {
        $either = new Left('error');

        $option = $either->unwrapLeft();

        static::assertTrue($option->isSome());
        static::assertSame('error', $option->unwrap());
    }

    public function testUnwrapRight(): void
    {
        $either = new Left('error');

        $option = $either->unwrapRight();

        static::assertTrue($option->isNone());
    }

    public function testMap(): void
    {
        $either = new Left('error');

        $mapped = $either->map(Str\length(...));

        static::assertInstanceOf(Left::class, $mapped);
        static::assertSame(5, $mapped->getLeft());
    }

    public function testMapLeft(): void
    {
        $either = new Left('error');

        $mapped = $either->mapLeft(Str\uppercase(...));

        static::assertInstanceOf(Left::class, $mapped);
        static::assertSame('ERROR', $mapped->getLeft());
    }

    public function testMapRight(): void
    {
        $either = new Left('error');

        $mapped = $either->mapRight(static fn($v) => $v * 2);

        static::assertSame($either, $mapped);
        static::assertSame('error', $mapped->getLeft());
    }

    public function testFlatMap(): void
    {
        $either = new Left('error');

        $result = $either->flatMap(static fn($v) => new Right('recovered'));

        static::assertInstanceOf(Right::class, $result);
        static::assertSame('recovered', $result->getRight());
    }

    public function testFlatMapLeft(): void
    {
        $either = new Left('error');

        $result = $either->flatMapLeft(static fn($v) => new Right('recovered from ' . $v));

        static::assertInstanceOf(Right::class, $result);
        static::assertSame('recovered from error', $result->getRight());
    }

    public function testFlatMapRight(): void
    {
        $either = new Left('error');

        $result = $either->flatMapRight(static fn($v) => new Left('should not happen'));

        static::assertSame($either, $result);
        static::assertSame('error', $result->getLeft());
    }

    public function testProceed(): void
    {
        $result = (new Left('error'))->proceed(static fn($v) => 'right: ' . $v, static fn($v) => 'left: ' . $v);

        static::assertSame('left: error', $result);
    }

    public function testApply(): void
    {
        $spy = new Ref('');

        $either = new Left('error');
        $actual = $either->apply(static function (string $value) use ($spy) {
            $spy->value = $value;
        });

        static::assertSame('error', $spy->value);
        static::assertSame($actual, $either);
    }

    public function testSwap(): void
    {
        $either = new Left('error');
        $swapped = $either->swap();

        static::assertInstanceOf(Right::class, $swapped);
        static::assertSame('error', $swapped->getRight());
    }

    public function testContainsLeft(): void
    {
        $either = new Left('error');

        static::assertTrue($either->containsLeft('error'));
        static::assertFalse($either->containsLeft('other'));
    }

    public function testContainsRight(): void
    {
        $either = new Left('error');

        static::assertFalse($either->containsRight('error'));
        static::assertFalse($either->containsRight('other'));
    }

    public function testComparable(): void
    {
        $a = new Left(2);

        static::assertInstanceOf(Comparable::class, $a);
        static::assertSame(Order::Equal, $a->compare(new Left(2)));
        static::assertSame(Order::Less, $a->compare(new Left(3)));
        static::assertSame(Order::Greater, $a->compare(new Left(1)));
        static::assertSame(Order::Less, $a->compare(new Right(1)));
    }

    public function testEquality(): void
    {
        $a = new Left('a');

        static::assertInstanceOf(Equable::class, $a);
        static::assertTrue($a->equals(new Left('a')));
        static::assertFalse($a->equals(new Left('b')));
        static::assertFalse($a->equals(new Right('a')));
    }
}
