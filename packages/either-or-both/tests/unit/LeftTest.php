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

#[CoversClass(Left::class)]
final class LeftTest extends TestCase
{
    public function testIsLeft(): void
    {
        static::assertTrue(new Left::<string>('a')->isLeft());
    }

    public function testIsRight(): void
    {
        static::assertFalse(new Left::<string>('a')->isRight());
    }

    public function testIsBoth(): void
    {
        static::assertFalse(new Left::<string>('a')->isBoth());
    }

    public function testHasLeft(): void
    {
        static::assertTrue(new Left::<string>('a')->hasLeft());
    }

    public function testHasRight(): void
    {
        static::assertFalse(new Left::<string>('a')->hasRight());
    }

    public function testGetLeft(): void
    {
        static::assertSame('a', new Left::<string>('a')->getLeft());
    }

    public function testGetRightThrows(): void
    {
        $this->expectException(EitherOrBoth\Exception\MissingRightException::class);
        $this->expectExceptionMessage('Attempting to get a right value from a left.');

        new Left::<string>('a')->getRight();
    }

    public function testUnwrapLeft(): void
    {
        $option = new Left::<string>('a')->unwrapLeft();

        static::assertTrue($option->isSome());
        static::assertSame('a', $option->unwrap());
    }

    public function testUnwrapRight(): void
    {
        static::assertTrue(new Left::<string>('a')->unwrapRight()->isNone());
    }

    public function testMap(): void
    {
        $result = new Left::<string>('hello')->map::<string>(Str\uppercase(...));

        static::assertInstanceOf(Left::class, $result);
        static::assertSame('HELLO', $result->getLeft());
    }

    public function testMapLeft(): void
    {
        $result = new Left::<string>('hello')->mapLeft::<string>(Str\uppercase(...));

        static::assertInstanceOf(Left::class, $result);
        static::assertSame('HELLO', $result->getLeft());
    }

    public function testMapRightIsNoOpAndReturnsSelf(): void
    {
        $left = new Left::<string>('hello');
        $spy = new Ref::<int>(0);

        $result = $left->mapRight::<string>(static function () use ($spy): string {
            $spy->value++;
            return 'not called';
        });

        static::assertSame($left, $result);
        static::assertSame(0, $spy->value);
    }

    public function testMapAnyOnlyCallsLeft(): void
    {
        $leftSpy = new Ref::<int>(0);
        $rightSpy = new Ref::<int>(0);

        $result = new Left::<string>('a')->mapAny::<string, string>(static function (string $v) use ($leftSpy): string {
            $leftSpy->value++;
            return $v . '!';
        }, static function () use ($rightSpy): string {
            $rightSpy->value++;
            return 'never';
        });

        static::assertInstanceOf(Left::class, $result);
        static::assertSame('a!', $result->getLeft());
        static::assertSame(1, $leftSpy->value);
        static::assertSame(0, $rightSpy->value);
    }

    public function testSwap(): void
    {
        $result = new Left::<string>('a')->swap();

        static::assertInstanceOf(Right::class, $result);
        static::assertSame('a', $result->getRight());
    }

    public function testProceedCallsLeftOnly(): void
    {
        $leftSpy = new Ref::<int>(0);
        $rightSpy = new Ref::<int>(0);
        $bothSpy = new Ref::<int>(0);

        $result = new Left::<string>('hello')->proceed::<string>(
            left: static function (string $v) use ($leftSpy): string {
                $leftSpy->value++;
                return 'left:' . $v;
            },
            right: static function () use ($rightSpy): string {
                $rightSpy->value++;
                return 'right';
            },
            both: static function () use ($bothSpy): string {
                $bothSpy->value++;
                return 'both';
            },
        );

        static::assertSame('left:hello', $result);
        static::assertSame(1, $leftSpy->value);
        static::assertSame(0, $rightSpy->value);
        static::assertSame(0, $bothSpy->value);
    }

    public function testApplyCallsClosureOnceWithLeftValueAndReturnsSelf(): void
    {
        $left = new Left::<string>('hello');
        $captured = new Ref::<string>('');
        $callCount = new Ref::<int>(0);

        $result = $left->apply(static function (string $v) use ($captured, $callCount): void {
            $captured->value = $v;
            $callCount->value++;
        });

        static::assertSame($left, $result);
        static::assertSame('hello', $captured->value);
        static::assertSame(1, $callCount->value);
    }

    public function testContainsLeft(): void
    {
        $left = new Left::<string>('hello');

        static::assertTrue($left->containsLeft('hello'));
        static::assertFalse($left->containsLeft('world'));
    }

    public function testContainsRightIsAlwaysFalse(): void
    {
        $left = new Left::<string>('hello');

        static::assertFalse($left->containsRight('hello'));
        static::assertFalse($left->containsRight('world'));
    }

    public function testEquals(): void
    {
        $a = new Left::<string>('a');

        static::assertInstanceOf(Equable::class, $a);
        static::assertTrue($a->equals(new Left::<string>('a')));
        static::assertFalse($a->equals(new Left::<string>('b')));
        static::assertFalse($a->equals(new Right::<string>('a')));
        static::assertFalse($a->equals(new Both::<string, string>('a', 'a')));
    }

    public function testLeftFactoryFunction(): void
    {
        $result = EitherOrBoth\left::<string>('hello');

        static::assertInstanceOf(Left::class, $result);
        static::assertSame('hello', $result->getLeft());
    }
}
