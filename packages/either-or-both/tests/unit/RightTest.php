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

#[CoversClass(Right::class)]
final class RightTest extends TestCase
{
    public function testIsLeft(): void
    {
        static::assertFalse(new Right::<string>('a')->isLeft());
    }

    public function testIsRight(): void
    {
        static::assertTrue(new Right::<string>('a')->isRight());
    }

    public function testIsBoth(): void
    {
        static::assertFalse(new Right::<string>('a')->isBoth());
    }

    public function testHasLeft(): void
    {
        static::assertFalse(new Right::<string>('a')->hasLeft());
    }

    public function testHasRight(): void
    {
        static::assertTrue(new Right::<string>('a')->hasRight());
    }

    public function testGetLeftThrows(): void
    {
        $this->expectException(EitherOrBoth\Exception\MissingLeftException::class);
        $this->expectExceptionMessage('Attempting to get a left value from a right.');

        new Right::<string>('a')->getLeft();
    }

    public function testGetRight(): void
    {
        static::assertSame('a', new Right::<string>('a')->getRight());
    }

    public function testUnwrapLeft(): void
    {
        static::assertTrue(new Right::<string>('a')->unwrapLeft()->isNone());
    }

    public function testUnwrapRight(): void
    {
        $option = new Right::<string>('a')->unwrapRight();

        static::assertTrue($option->isSome());
        static::assertSame('a', $option->unwrap());
    }

    public function testMap(): void
    {
        $result = new Right::<string>('hello')->map::<string>(Str\uppercase(...));

        static::assertInstanceOf(Right::class, $result);
        static::assertSame('HELLO', $result->getRight());
    }

    public function testMapLeftIsNoOpAndReturnsSelf(): void
    {
        $right = new Right::<string>('hello');
        $spy = new Ref::<int>(0);

        $result = $right->mapLeft::<string>(static function () use ($spy): string {
            $spy->value++;
            return 'not called';
        });

        static::assertSame($right, $result);
        static::assertSame(0, $spy->value);
    }

    public function testMapRight(): void
    {
        $result = new Right::<string>('hello')->mapRight::<string>(Str\uppercase(...));

        static::assertInstanceOf(Right::class, $result);
        static::assertSame('HELLO', $result->getRight());
    }

    public function testMapAnyOnlyCallsRight(): void
    {
        $leftSpy = new Ref::<int>(0);
        $rightSpy = new Ref::<int>(0);

        $result = new Right::<string>('a')->mapAny::<string, string>(static function () use ($leftSpy): string {
            $leftSpy->value++;
            return 'never';
        }, static function (string $v) use ($rightSpy): string {
            $rightSpy->value++;
            return $v . '!';
        });

        static::assertInstanceOf(Right::class, $result);
        static::assertSame('a!', $result->getRight());
        static::assertSame(0, $leftSpy->value);
        static::assertSame(1, $rightSpy->value);
    }

    public function testSwap(): void
    {
        $result = new Right::<string>('a')->swap();

        static::assertInstanceOf(Left::class, $result);
        static::assertSame('a', $result->getLeft());
    }

    public function testProceedCallsRightOnly(): void
    {
        $leftSpy = new Ref::<int>(0);
        $rightSpy = new Ref::<int>(0);
        $bothSpy = new Ref::<int>(0);

        $result = new Right::<string>('hello')->proceed::<string>(
            left: static function () use ($leftSpy): string {
                $leftSpy->value++;
                return 'left';
            },
            right: static function (string $v) use ($rightSpy): string {
                $rightSpy->value++;
                return 'right:' . $v;
            },
            both: static function () use ($bothSpy): string {
                $bothSpy->value++;
                return 'both';
            },
        );

        static::assertSame('right:hello', $result);
        static::assertSame(0, $leftSpy->value);
        static::assertSame(1, $rightSpy->value);
        static::assertSame(0, $bothSpy->value);
    }

    public function testApplyCallsClosureOnceWithRightValueAndReturnsSelf(): void
    {
        $right = new Right::<string>('hello');
        $captured = new Ref::<string>('');
        $callCount = new Ref::<int>(0);

        $result = $right->apply(static function (string $v) use ($captured, $callCount): void {
            $captured->value = $v;
            $callCount->value++;
        });

        static::assertSame($right, $result);
        static::assertSame('hello', $captured->value);
        static::assertSame(1, $callCount->value);
    }

    public function testContainsLeftIsAlwaysFalse(): void
    {
        $right = new Right::<string>('hello');

        static::assertFalse($right->containsLeft('hello'));
        static::assertFalse($right->containsLeft('world'));
    }

    public function testContainsRight(): void
    {
        $right = new Right::<string>('hello');

        static::assertTrue($right->containsRight('hello'));
        static::assertFalse($right->containsRight('world'));
    }

    public function testEquals(): void
    {
        $a = new Right::<string>('a');

        static::assertInstanceOf(Equable::class, $a);
        static::assertTrue($a->equals(new Right::<string>('a')));
        static::assertFalse($a->equals(new Right::<string>('b')));
        static::assertFalse($a->equals(new Left::<string>('a')));
        static::assertFalse($a->equals(new Both::<string, string>('a', 'a')));
    }

    public function testRightFactoryFunction(): void
    {
        $result = EitherOrBoth\right::<string>('hello');

        static::assertInstanceOf(Right::class, $result);
        static::assertSame('hello', $result->getRight());
    }
}
