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

use function Psl\EitherOrBoth\left;

#[CoversClass(Left::class)]
final class LeftTest extends TestCase
{
    public function testIsLeft(): void
    {
        static::assertTrue(new Left('a')->isLeft());
    }

    public function testIsRight(): void
    {
        static::assertFalse(new Left('a')->isRight());
    }

    public function testIsBoth(): void
    {
        static::assertFalse(new Left('a')->isBoth());
    }

    public function testHasLeft(): void
    {
        static::assertTrue(new Left('a')->hasLeft());
    }

    public function testHasRight(): void
    {
        static::assertFalse(new Left('a')->hasRight());
    }

    public function testGetLeft(): void
    {
        static::assertSame('a', new Left('a')->getLeft());
    }

    public function testGetRightThrows(): void
    {
        $this->expectException(EitherOrBoth\Exception\MissingRightException::class);
        $this->expectExceptionMessage('Attempting to get a right value from a left.');

        new Left('a')->getRight();
    }

    public function testUnwrapLeft(): void
    {
        $option = new Left('a')->unwrapLeft();

        static::assertTrue($option->isSome());
        static::assertSame('a', $option->unwrap());
    }

    public function testUnwrapRight(): void
    {
        static::assertTrue(new Left('a')->unwrapRight()->isNone());
    }

    public function testMap(): void
    {
        $result = new Left('hello')->map(Str\uppercase(...));

        static::assertInstanceOf(Left::class, $result);
        static::assertSame('HELLO', $result->getLeft());
    }

    public function testMapLeft(): void
    {
        $result = new Left('hello')->mapLeft(Str\uppercase(...));

        static::assertInstanceOf(Left::class, $result);
        static::assertSame('HELLO', $result->getLeft());
    }

    public function testMapRightIsNoOpAndReturnsSelf(): void
    {
        $left = new Left('hello');
        $spy = new Ref(0);

        $result = $left->mapRight(static function () use ($spy): string {
            $spy->value++;
            return 'not called';
        });

        static::assertSame($left, $result);
        static::assertSame(0, $spy->value);
    }

    public function testMapAnyOnlyCallsLeft(): void
    {
        $leftSpy = new Ref(0);
        $rightSpy = new Ref(0);

        $result = new Left('a')->mapAny(static function (string $v) use ($leftSpy): string {
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
        $result = new Left('a')->swap();

        static::assertInstanceOf(Right::class, $result);
        static::assertSame('a', $result->getRight());
    }

    public function testProceedCallsLeftOnly(): void
    {
        $leftSpy = new Ref(0);
        $rightSpy = new Ref(0);
        $bothSpy = new Ref(0);

        $result = new Left('hello')->proceed(
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
        $left = new Left('hello');
        $captured = new Ref('');
        $callCount = new Ref(0);

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
        $left = new Left('hello');

        static::assertTrue($left->containsLeft('hello'));
        static::assertFalse($left->containsLeft('world'));
    }

    public function testContainsRightIsAlwaysFalse(): void
    {
        $left = new Left('hello');

        static::assertFalse($left->containsRight('hello'));
        static::assertFalse($left->containsRight('world'));
    }

    public function testEquals(): void
    {
        $a = new Left('a');

        static::assertInstanceOf(Equable::class, $a);
        static::assertTrue($a->equals(new Left('a')));
        static::assertFalse($a->equals(new Left('b')));
        static::assertFalse($a->equals(new Right('a')));
        static::assertFalse($a->equals(new Both('a', 'a')));
    }

    public function testLeftFactoryFunction(): void
    {
        $result = left('hello');

        static::assertInstanceOf(Left::class, $result);
        static::assertSame('hello', $result->getLeft());
    }
}
