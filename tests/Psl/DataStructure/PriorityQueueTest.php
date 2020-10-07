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

        self::assertCount(3, $queue);
        self::assertSame('hello', $queue->dequeue());
        self::assertCount(2, $queue);
        self::assertSame('hey', $queue->dequeue());
        self::assertCount(1, $queue);
        self::assertSame('hi', $queue->dequeue());
    }

    public function testMultipleNodesWithSamePriority(): void
    {
        $queue = new DataStructure\PriorityQueue();
        $queue->enqueue('hi', 1);
        $queue->enqueue('hey', 1);
        $queue->enqueue('hello', 1);

        self::assertCount(3, $queue);
        self::assertSame('hi', $queue->dequeue());
        self::assertCount(2, $queue);
        self::assertSame('hey', $queue->dequeue());
        self::assertCount(1, $queue);
        self::assertSame('hello', $queue->dequeue());
    }

    public function testPeekDoesNotRemoveTheNode(): void
    {
        $queue = new DataStructure\PriorityQueue();
        $queue->enqueue('hi', 1);
        $queue->enqueue('hey', 2);
        $queue->enqueue('hello', 3);

        self::assertCount(3, $queue);
        self::assertSame('hello', $queue->peek());
        self::assertCount(3, $queue);
        self::assertSame('hello', $queue->peek());
    }

    public function testPeekReturnsNullWhenTheQueueIsEmpty(): void
    {
        $queue = new DataStructure\PriorityQueue();

        self::assertCount(0, $queue);
        self::assertNull($queue->peek());
    }

    public function testPullDoesRemoveTheNode(): void
    {
        $queue = new DataStructure\PriorityQueue();
        $queue->enqueue('hi', 1);
        $queue->enqueue('hey', 2);
        $queue->enqueue('hello', 3);

        self::assertCount(3, $queue);
        self::assertSame('hello', $queue->pull());
        self::assertCount(2, $queue);
        self::assertSame('hey', $queue->pull());
        self::assertCount(1, $queue);
        self::assertSame('hi', $queue->pull());
        self::assertCount(0, $queue);
        self::assertNull($queue->pull());
    }

    public function testPullReturnsNullWhenTheQueueIsEmpty(): void
    {
        $queue = new DataStructure\PriorityQueue();

        self::assertCount(0, $queue);
        self::assertNull($queue->pull());
    }

    public function testDequeueThrowsWhenTheQueueIsEmpty(): void
    {
        $queue = new DataStructure\PriorityQueue();

        $this->expectException(Psl\Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Cannot dequeue a node from an empty Queue.');

        $queue->dequeue();
    }
}
