<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\DataStructure;

use PHPUnit\Framework\TestCase;
use Psl\DataStructure;

final class QueueTest extends TestCase
{
    public function testEnqueueAndDequeue(): void
    {
        $queue = DataStructure\Queue::default();
        $queue->enqueue('hello');
        $queue->enqueue('hey');
        $queue->enqueue('hi');

        static::assertCount(3, $queue);
        static::assertSame('hello', $queue->dequeue());
        static::assertCount(2, $queue);
        static::assertSame('hey', $queue->dequeue());
        static::assertCount(1, $queue);
        static::assertSame('hi', $queue->dequeue());
    }

    public function testPeekDoesNotRemoveTheNode(): void
    {
        $queue = DataStructure\Queue::default();
        $queue->enqueue('hello');
        $queue->enqueue('hey');
        $queue->enqueue('hi');

        static::assertCount(3, $queue);
        static::assertSame('hello', $queue->peek());
        static::assertCount(3, $queue);
        static::assertSame('hello', $queue->peek());
    }

    public function testPeekReturnsNullWhenTheQueueIsEmpty(): void
    {
        $queue = DataStructure\Queue::default();

        static::assertCount(0, $queue);
        static::assertNull($queue->peek());
    }

    public function testPullDoesRemoveTheNode(): void
    {
        $queue = new DataStructure\Queue();
        $queue->enqueue('hello');
        $queue->enqueue('hey');
        $queue->enqueue('hi');

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
        $queue = new DataStructure\Queue();

        static::assertCount(0, $queue);
        static::assertNull($queue->pull());
    }

    public function testDequeueThrowsWhenTheQueueIsEmpty(): void
    {
        $queue = new DataStructure\Queue();

        $this->expectException(DataStructure\Exception\UnderflowException::class);
        $this->expectExceptionMessage('Cannot dequeue a node from an empty queue.');

        $queue->dequeue();
    }
}
