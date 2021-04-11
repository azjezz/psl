<?php

declare(strict_types=1);

namespace Psl\DataStructure;

use PHPUnit\Framework\TestCase;
use Psl;
use Psl\DataStructure;

final class QueueTest extends TestCase
{
    public function testEnqueueAndDequeue(): void
    {
        $queue = new DataStructure\Queue();
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
        $queue = new DataStructure\Queue();
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
        $queue = new DataStructure\Queue();

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

        $this->expectException(Psl\Exception\InvariantViolationException::class);
        $this->expectExceptionMessage('Cannot dequeue a node from an empty Queue.');

        $queue->dequeue();
    }
}
