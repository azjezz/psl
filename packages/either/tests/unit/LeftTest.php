<?php

declare(strict_types=1);

namespace Psl\Either\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psl\Comparison\Comparable;
use Psl\Comparison\Equable;
use Psl\Comparison\Order;
use Psl\Either;
use Psl\Either\Left;
use Psl\Either\Right;
use Psl\Ref;
use Psl\Str;

#[CoversClass(Left::class)]
final class LeftTest extends TestCase
{
    public function testIsLeft(): void
    {
        $either = new Left::<string>('error');

        static::assertTrue($either->isLeft());
        static::assertFalse($either->isRight());
    }

    public function testGetLeft(): void
    {
        $either = new Left::<string>('error');

        static::assertSame('error', $either->getLeft());
    }

    public function testGetRight(): void
    {
        $either = new Left::<string>('error');

        $this->expectException(Either\Exception\LeftException::class);
        $this->expectExceptionMessage('Attempting to get a right value from a left either.');

        $either->getRight();
    }

    public function testGetLeftOr(): void
    {
        $either = new Left::<string>('error');

        static::assertSame('error', $either->getLeftOr::<string>('default'));
    }

    public function testGetRightOr(): void
    {
        $either = new Left::<string>('error');

        static::assertSame('default', $either->getRightOr::<string>('default'));
    }

    public function testGetLeftOrElse(): void
    {
        $either = new Left::<string>('error');

        static::assertSame('error', $either->getLeftOrElse(static fn($v) => 'computed'));
    }

    public function testGetRightOrElse(): void
    {
        $either = new Left::<string>('error');

        static::assertSame('computed from error', $either->getRightOrElse::<string>(static fn($v) => 'computed from ' . $v));
    }

    public function testUnwrapLeft(): void
    {
        $either = new Left::<string>('error');

        $option = $either->unwrapLeft();

        static::assertTrue($option->isSome());
        static::assertSame('error', $option->unwrap());
    }

    public function testUnwrapRight(): void
    {
        $either = new Left::<string>('error');

        $option = $either->unwrapRight();

        static::assertTrue($option->isNone());
    }

    public function testMap(): void
    {
        $either = new Left::<string>('error');

        $mapped = $either->map::<int>(Str\length(...));

        static::assertInstanceOf(Left::class, $mapped);
        static::assertSame(5, $mapped->getLeft());
    }

    public function testMapLeft(): void
    {
        $either = new Left::<string>('error');

        $mapped = $either->mapLeft::<string>(Str\uppercase(...));

        static::assertInstanceOf(Left::class, $mapped);
        static::assertSame('ERROR', $mapped->getLeft());
    }

    public function testMapRight(): void
    {
        $either = new Left::<string>('error');

        $mapped = $either->mapRight::<int>(static fn($v) => $v * 2);

        static::assertSame($either, $mapped);
        static::assertSame('error', $mapped->getLeft());
    }

    public function testFlatMap(): void
    {
        $either = new Left::<string>('error');

        $result = $either->flatMap::<never, string>(static fn($v) => new Right::<string>('recovered'));

        static::assertInstanceOf(Right::class, $result);
        static::assertSame('recovered', $result->getRight());
    }

    public function testFlatMapLeft(): void
    {
        $either = new Left::<string>('error');

        $result = $either->flatMapLeft::<never, string>(static fn($v) => new Right::<string>('recovered from ' . $v));

        static::assertInstanceOf(Right::class, $result);
        static::assertSame('recovered from error', $result->getRight());
    }

    public function testFlatMapRight(): void
    {
        $either = new Left::<string>('error');

        $result = $either->flatMapRight::<string, never>(static fn($v) => new Left::<string>('should not happen'));

        static::assertSame($either, $result);
        static::assertSame('error', $result->getLeft());
    }

    public function testProceed(): void
    {
        $result = new Left::<string>('error')->proceed::<string>(static fn($v) => 'right: ' . $v, static fn($v) => 'left: ' . $v);

        static::assertSame('left: error', $result);
    }

    public function testApply(): void
    {
        $spy = new Ref::<string>('');

        $either = new Left::<string>('error');
        $actual = $either->apply(static function (string $value) use ($spy) {
            $spy->value = $value;
        });

        static::assertSame('error', $spy->value);
        static::assertSame($actual, $either);
    }

    public function testSwap(): void
    {
        $either = new Left::<string>('error');
        $swapped = $either->swap();

        static::assertInstanceOf(Right::class, $swapped);
        static::assertSame('error', $swapped->getRight());
    }

    public function testContainsLeft(): void
    {
        $either = new Left::<string>('error');

        static::assertTrue($either->containsLeft('error'));
        static::assertFalse($either->containsLeft('other'));
    }

    public function testContainsRight(): void
    {
        $either = new Left::<string>('error');

        static::assertFalse($either->containsRight('error'));
        static::assertFalse($either->containsRight('other'));
    }

    public function testComparable(): void
    {
        $a = new Left::<int>(2);

        static::assertInstanceOf(Comparable::class, $a);
        static::assertSame(Order::Equal, $a->compare(new Left::<int>(2)));
        static::assertSame(Order::Less, $a->compare(new Left::<int>(3)));
        static::assertSame(Order::Greater, $a->compare(new Left::<int>(1)));
        static::assertSame(Order::Less, $a->compare(new Right::<int>(1)));
    }

    public function testEquality(): void
    {
        $a = new Left::<string>('a');

        static::assertInstanceOf(Equable::class, $a);
        static::assertTrue($a->equals(new Left::<string>('a')));
        static::assertFalse($a->equals(new Left::<string>('b')));
        static::assertFalse($a->equals(new Right::<string>('a')));
    }
}
