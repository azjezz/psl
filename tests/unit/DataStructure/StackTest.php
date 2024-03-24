<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\DataStructure;

use PHPUnit\Framework\TestCase;
use Psl\DataStructure;

final class StackTest extends TestCase
{
    public function testPushAndPop(): void
    {
        $stack = DataStructure\Stack::default();
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
        $stack = DataStructure\Stack::default();

        static::assertNull($stack->peek());

        $stack->push('hello');

        static::assertNotNull($stack->peek());
        static::assertSame('hello', $stack->peek());

        static::assertSame('hello', $stack->pop());

        static::assertNull($stack->peek());
    }

    public function testPopThrowsForEmptyStack(): void
    {
        $stack = DataStructure\Stack::default();
        $stack->push('hello');

        static::assertSame('hello', $stack->pop());
        static::assertNull($stack->peek());

        $this->expectException(DataStructure\Exception\UnderflowException::class);
        $this->expectExceptionMessage('Cannot pop an item from an empty stack.');

        $stack->pop();
    }

    public function testPullReturnsNullForEmptyStack(): void
    {
        $stack = new DataStructure\Stack();
        $stack->push('hello');

        static::assertSame('hello', $stack->pull());
        static::assertNull($stack->pull());
    }

    public function testCount(): void
    {
        $stack = new DataStructure\Stack();
        static::assertSame(0, $stack->count());

        $stack->push('hello');
        static::assertSame(1, $stack->count());
        $stack->pop();
        static::assertSame(0, $stack->count());
        $stack->push('hello');
        $stack->push('hello');
        $stack->push('hello');
        static::assertSame(3, $stack->count());
        $stack->pop();
        static::assertSame(2, $stack->count());
    }
}
