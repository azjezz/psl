<?php

declare(strict_types=1);

namespace Psl\DataStructure;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\DataStructure;

final class PriorityQueueTest extends TestCase
{
    public function testEnqueueAndDequeue(): void
    {
        $queue = new DataStructure\PriorityQueue();
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

    public function testMultipleNodesWithSamePriority(): void
    {
        $queue = new DataStructure\PriorityQueue();
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
        $queue = new DataStructure\PriorityQueue();
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
        $queue = new DataStructure\PriorityQueue();

        static::assertCount(0, $queue);
        static::assertNull($queue->peek());
    }

    public function testPullDoesRemoveTheNode(): void
    {
        $queue = new DataStructure\PriorityQueue();
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

        $this->expectException(Psl\Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Cannot dequeue a node from an empty Queue.');

        $queue->dequeue();
    }
}
