<?php

declare(strict_types=1);

namespace Psl\Tests\DataStructure;

use Psl;
use Psl\DataStructure\Stack;
use PHPUnit\Framework\TestCase;

final class StackTest extends TestCase
{
    public function testPushAndPop(): void
    {
        $stack = new Stack();
        $stack->push('hello');
        $stack->push('hey');
        $stack->push('hi');

        self::assertCount(3, $stack);

        self::assertSame('hi', $stack->peek());

        self::assertSame('hi', $stack->pop());
        self::assertSame('hey', $stack->pop());
        self::assertSame('hello', $stack->pop());

        self::assertNull($stack->pull());
    }

    public function testPeek(): void
    {
        $stack = new Stack();

        self::assertNull($stack->peek());

        $stack->push('hello');

        self::assertNotNull($stack->peek());
        self::assertSame('hello', $stack->peek());
    }

    public function testPopThrowsForEmptyStack(): void
    {
        $stack = new Stack();
        $stack->push('hello');

        self::assertSame('hello', $stack->pop());

        $this->expectException(Psl\Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Cannot pop an item from an empty Stack.');

        $stack->pop();
    }

    public function testPullReturnsNullForEmptyStack(): void
    {
        $stack = new Stack();
        $stack->push('hello');

        self::assertSame('hello', $stack->pull());
        self::assertNull($stack->pull());
    }

    public function testCount(): void
    {
        $stack = new Stack();
        self::assertSame(0, $stack->count());

        $stack->push('hello');
        self::assertSame(1, $stack->count());
    }
}
