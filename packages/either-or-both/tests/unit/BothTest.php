<?php

declare(strict_types=1);

namespace Psl\EitherOrBoth\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psl\Comparison\Equable;
use Psl\EitherOrBoth;
use Psl\EitherOrBoth\Both;
use Psl\EitherOrBoth\Left;
use Psl\EitherOrBoth\Right;
use Psl\Ref;
use Psl\Str;

#[CoversClass(Both::class)]
final class BothTest extends TestCase
{
    public function testIsLeft(): void
    {
        static::assertFalse(new Both('l', 'r')->isLeft());
    }

    public function testIsRight(): void
    {
        static::assertFalse(new Both('l', 'r')->isRight());
    }

    public function testIsBoth(): void
    {
        static::assertTrue(new Both('l', 'r')->isBoth());
    }

    public function testHasLeft(): void
    {
        static::assertTrue(new Both('l', 'r')->hasLeft());
    }

    public function testHasRight(): void
    {
        static::assertTrue(new Both('l', 'r')->hasRight());
    }

    public function testGetLeft(): void
    {
        static::assertSame('l', new Both('l', 'r')->getLeft());
    }

    public function testGetRight(): void
    {
        static::assertSame('r', new Both('l', 'r')->getRight());
    }

    public function testUnwrapLeft(): void
    {
        $option = new Both('l', 'r')->unwrapLeft();

        static::assertTrue($option->isSome());
        static::assertSame('l', $option->unwrap());
    }

    public function testUnwrapRight(): void
    {
        $option = new Both('l', 'r')->unwrapRight();

        static::assertTrue($option->isSome());
        static::assertSame('r', $option->unwrap());
    }

    public function testMapRunsClosureTwice(): void
    {
        $callCount = new Ref(0);
        $closure = static function (string $v) use ($callCount): string {
            $callCount->value++;
            return Str\uppercase($v);
        };

        $result = new Both('hello', 'world')->map($closure);

        static::assertInstanceOf(Both::class, $result);
        static::assertSame('HELLO', $result->getLeft());
        static::assertSame('WORLD', $result->getRight());
        static::assertSame(2, $callCount->value);
    }

    public function testMapLeftKeepsRightUntouched(): void
    {
        $result = new Both('hello', 'world')->mapLeft(Str\uppercase(...));

        static::assertInstanceOf(Both::class, $result);
        static::assertSame('HELLO', $result->getLeft());
        static::assertSame('world', $result->getRight());
    }

    public function testMapRightKeepsLeftUntouched(): void
    {
        $result = new Both('hello', 'world')->mapRight(Str\uppercase(...));

        static::assertInstanceOf(Both::class, $result);
        static::assertSame('hello', $result->getLeft());
        static::assertSame('WORLD', $result->getRight());
    }

    public function testMapAnyCallsBothIndependently(): void
    {
        $leftSpy = new Ref(0);
        $rightSpy = new Ref(0);

        $result = new Both('hello', 'world')->mapAny(static function (string $v) use ($leftSpy): int {
            $leftSpy->value++;
            return Str\length($v);
        }, static function (string $v) use ($rightSpy): string {
            $rightSpy->value++;
            return Str\uppercase($v);
        });

        static::assertInstanceOf(Both::class, $result);
        static::assertSame(5, $result->getLeft());
        static::assertSame('WORLD', $result->getRight());
        static::assertSame(1, $leftSpy->value);
        static::assertSame(1, $rightSpy->value);
    }

    public function testSwap(): void
    {
        $result = new Both('l', 'r')->swap();

        static::assertInstanceOf(Both::class, $result);
        static::assertSame('r', $result->getLeft());
        static::assertSame('l', $result->getRight());
    }

    public function testSwapRoundTripIsIdentity(): void
    {
        $original = new Both('l', 'r');
        $swappedTwice = $original->swap()->swap();

        static::assertTrue($original->equals($swappedTwice));
    }

    public function testProceedCallsBothOnly(): void
    {
        $leftSpy = new Ref(0);
        $rightSpy = new Ref(0);
        $bothSpy = new Ref(0);

        $result = new Both('hello', 'world')->proceed(
            left: static function () use ($leftSpy): string {
                $leftSpy->value++;
                return 'left';
            },
            right: static function () use ($rightSpy): string {
                $rightSpy->value++;
                return 'right';
            },
            both: static function (string $l, string $r) use ($bothSpy): string {
                $bothSpy->value++;
                return 'both:' . $l . ',' . $r;
            },
        );

        static::assertSame('both:hello,world', $result);
        static::assertSame(0, $leftSpy->value);
        static::assertSame(0, $rightSpy->value);
        static::assertSame(1, $bothSpy->value);
    }

    public function testApplyRunsClosureTwiceAndReturnsSelf(): void
    {
        $both = new Both('hello', 'world');
        $captured = new Ref([]);

        $result = $both->apply(static function (string $v) use ($captured): void {
            $captured->value = [...$captured->value, $v];
        });

        static::assertSame($both, $result);
        static::assertSame(['hello', 'world'], $captured->value);
    }

    public function testContainsLeft(): void
    {
        $both = new Both('l', 'r');

        static::assertTrue($both->containsLeft('l'));
        static::assertFalse($both->containsLeft('r'));
        static::assertFalse($both->containsLeft('other'));
    }

    public function testContainsRight(): void
    {
        $both = new Both('l', 'r');

        static::assertTrue($both->containsRight('r'));
        static::assertFalse($both->containsRight('l'));
        static::assertFalse($both->containsRight('other'));
    }

    public function testEquals(): void
    {
        $a = new Both('l', 'r');

        static::assertInstanceOf(Equable::class, $a);
        static::assertTrue($a->equals(new Both('l', 'r')));
        static::assertFalse($a->equals(new Both('l', 'different')));
        static::assertFalse($a->equals(new Both('different', 'r')));
        static::assertFalse($a->equals(new Both('r', 'l'))); // swapped values are not equal
        static::assertFalse($a->equals(new Left('l')));
        static::assertFalse($a->equals(new Right('r')));
    }

    public function testBothFactoryFunction(): void
    {
        $result = EitherOrBoth\both('l', 'r');

        static::assertInstanceOf(Both::class, $result);
        static::assertSame('l', $result->getLeft());
        static::assertSame('r', $result->getRight());
    }
}
