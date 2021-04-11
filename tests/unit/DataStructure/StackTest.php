<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\DataStructure;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\DataStructure\Stack;

final class StackTest extends TestCase
{
    public function testPushAndPop(): void
    {
        $stack = new Stack();
        $stack->push('hello');
        $stack->push('hey');
        $stack->push('hi');

        static::assertCount(3, $stack);

        static::assertSame('hi', $stack->peek());

        static::assertSame('hi', $stack->pop());
        static::assertSame('hey', $stack->pop());
        static::assertSame('hello', $stack->pop());

        static::assertNull($stack->pull());
    }

    public function testPeek(): void
    {
        $stack = new Stack();

        static::assertNull($stack->peek());

        $stack->push('hello');

        static::assertNotNull($stack->peek());
        static::assertSame('hello', $stack->peek());
    }

    public function testPopThrowsForEmptyStack(): void
    {
        $stack = new Stack();
        $stack->push('hello');

        static::assertSame('hello', $stack->pop());

        $this->expectException(Psl\Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Cannot pop an item from an empty Stack.');

        $stack->pop();
    }

    public function testPullReturnsNullForEmptyStack(): void
    {
        $stack = new Stack();
        $stack->push('hello');

        static::assertSame('hello', $stack->pull());
        static::assertNull($stack->pull());
    }

    public function testCount(): void
    {
        $stack = new Stack();
        static::assertSame(0, $stack->count());

        $stack->push('hello');
        static::assertSame(1, $stack->count());
    }
}
