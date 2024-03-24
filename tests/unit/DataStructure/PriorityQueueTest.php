<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\DataStructure;

use PHPUnit\Framework\TestCase;
use Psl\DataStructure;

final class PriorityQueueTest extends TestCase
{
    public function testEnqueueAndDequeue(): void
    {
        $queue = DataStructure\PriorityQueue::default();
        $queue->enqueue('hi', 1);
        $queue->enqueue('hey', 2);
        $queue->enqueue('hello', 3);

        static::assertCount(3, $queue);
        static::assertSame('hello', $queue->dequeue());
        static::assertCount(2, $queue);
        static::assertSame('hey', $queue->dequeue());
        static::assertCount(1, $queue);
        static::assertSame('hi', $queue->dequeue());
    }

    public function testDefaultEnqueueSettings(): void
    {
        $queue = DataStructure\PriorityQueue::default();
        $queue->enqueue('hey');
        $queue->enqueue('ho', 0);
        $queue->enqueue('hi', -1);
        $queue->enqueue('hello', 1);

        static::assertCount(4, $queue);
        static::assertSame('hello', $queue->dequeue());
        static::assertCount(3, $queue);
        static::assertSame('hey', $queue->dequeue());
        static::assertCount(2, $queue);
        static::assertSame('ho', $queue->dequeue());
        static::assertCount(1, $queue);
        static::assertSame('hi', $queue->dequeue());
    }

    public function testMultipleNodesWithSamePriority(): void
    {
        $queue = DataStructure\PriorityQueue::default();
        $queue->enqueue('hi', 1);
        $queue->enqueue('hey', 1);
        $queue->enqueue('hello', 1);

        static::assertCount(3, $queue);
        static::assertSame('hi', $queue->dequeue());
        static::assertCount(2, $queue);
        static::assertSame('hey', $queue->dequeue());
        static::assertCount(1, $queue);
        static::assertSame('hello', $queue->dequeue());
    }

    public function testPeekDoesNotRemoveTheNode(): void
    {
        $queue = DataStructure\PriorityQueue::default();
        $queue->enqueue('hi', 1);
        $queue->enqueue('hey', 2);
        $queue->enqueue('hello', 3);

        static::assertCount(3, $queue);
        static::assertSame('hello', $queue->peek());
        static::assertCount(3, $queue);
        static::assertSame('hello', $queue->peek());
    }

    public function testPeekReturnsNullWhenTheQueueIsEmpty(): void
    {
        $queue = DataStructure\PriorityQueue::default();

        static::assertCount(0, $queue);
        static::assertNull($queue->peek());
    }

    public function testPullDoesRemoveTheNode(): void
    {
        $queue = DataStructure\PriorityQueue::default();
        $queue->enqueue('hi', 1);
        $queue->enqueue('hey', 2);
        $queue->enqueue('hello', 3);

        static::assertCount(3, $queue);
        static::assertSame('hello', $queue->pull());
        static::assertCount(2, $queue);
        static::assertSame('hey', $queue->pull());
        static::assertCount(1, $queue);
        static::assertSame('hi', $queue->pull());
        static::assertCount(0, $queue);
        static::assertNull($queue->pull());
    }

    public function testPullReturnsNullWhenTheQueueIsEmpty(): void
    {
        $queue = new DataStructure\PriorityQueue();

        static::assertCount(0, $queue);
        static::assertNull($queue->pull());
    }

    public function testDequeueThrowsWhenTheQueueIsEmpty(): void
    {
        $queue = new DataStructure\PriorityQueue();

        $this->expectException(DataStructure\Exception\UnderflowException::class);
        $this->expectExceptionMessage('Cannot dequeue a node from an empty queue.');

        $queue->dequeue();
    }
}
