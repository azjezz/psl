<?php

declare(strict_types=1);

namespace Psl\DataStructure;

use Override;

use function array_shift;
use function count;

/**
 * A basic implementation of a queue data structure ( FIFO ).
 *
 * @api
 */
final class Queue<T> implements QueueInterface<T>
{
    /**
     * @var list<T>
     */
    private array $queue = [];

    /**
     * Provides a default instance of the {@see Queue}.
     *
     * @return static A new instance of {@see Queue}, devoid of any nodes.
     *
     * @pure
     */
    #[Override]
    public static function default(): static
    {
        return new self::<T>();
    }

    /**
     * Adds a node to the queue.
     *
     * @psalm-external-mutation-free
     */
    #[Override]
    public function enqueue(T $node): void
    {
        $this->queue[] = $node;
    }

    /**
     * Retrieves, but does not remove, the node at the head of this queue,
     * or returns null if this queue is empty.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function peek(): null|T
    {
        return $this->queue[0] ?? null;
    }

    /**
     * Retrieves and removes the node at the head of this queue,
     * or returns null if this queue is empty.
     *
     * @psalm-external-mutation-free
     */
    #[Override]
    public function pull(): null|T
    {
        return array_shift($this->queue);
    }

    /**
     * Dequeues a node from the queue.
     *
     * @throws Exception\UnderflowException If the queue is empty.
     *
     * @psalm-external-mutation-free
     */
    #[Override]
    public function dequeue(): T
    {
        if ([] === $this->queue) {
            throw new Exception\UnderflowException('Cannot dequeue a node from an empty queue.');
        }

        /** @var T */
        return array_shift($this->queue);
    }

    /**
     * Count the nodes in the queue.
     *
     * @return int<0, max>
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function count(): int
    {
        return count($this->queue);
    }
}
