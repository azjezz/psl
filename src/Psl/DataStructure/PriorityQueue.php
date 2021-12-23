<?php

declare(strict_types=1);

namespace Psl\DataStructure;

use Psl\Math;

use function array_keys;
use function array_shift;
use function count;

/**
 * @template T
 *
 * @implements PriorityQueueInterface<T>
 */
final class PriorityQueue implements PriorityQueueInterface
{
    /**
     * @var array<int, non-empty-list<T>>
     */
    private array $queue = [];

    /**
     * Adds a node to the queue.
     *
     * @param T $node
     */
    public function enqueue(mixed $node, int $priority = 0): void
    {
        $nodes = $this->queue[$priority] ?? [];
        $nodes[] = $node;

        $this->queue[$priority] = $nodes;
    }

    /**
     * Retrieves, but does not remove, the node at the head of this queue,
     * or returns null if this queue is empty.
     *
     * @return null|T
     */
    public function peek(): mixed
    {
        if (0 === $this->count()) {
            return null;
        }

        $keys = array_keys($this->queue);

        // Retrieve the highest priority.
        $priority = Math\max($keys) ?? 0;

        // Retrieve the list of nodes with the priority `$priority`.
        $nodes = $this->queue[$priority] ?? [];

        // Retrieve the first node of the list.
        return $nodes[0] ?? null;
    }

    /**
     * Retrieves and removes the node at the head of this queue,
     * or returns null if this queue is empty.
     *
     * @return null|T
     */
    public function pull(): mixed
    {
        try {
            return $this->dequeue();
        } catch (Exception\UnderflowException) {
            return null;
        }
    }

    /**
     * Dequeues a node from the queue.
     *
     * @throws Exception\UnderflowException If the queue is empty.
     *
     * @return T
     */
    public function dequeue(): mixed
    {
        if (0 === $this->count()) {
            throw new Exception\UnderflowException('Cannot dequeue a node from an empty queue.');
        }

        /**
         * retrieve the highest priority.
         *
         * @var int
         */
        $priority = Math\max(array_keys($this->queue));
        /**
         * retrieve the list of nodes with the priority `$priority`.
         */
        $nodes = $this->queue[$priority];
        /**
         * shift the first node out.
         */
        $node = array_shift($nodes);
        /**
         * If the list contained only this node, remove the list of nodes with priority `$priority`.
         */
        if ([] === $nodes) {
            unset($this->queue[$priority]);
        } else {
            $this->queue[$priority] = $nodes;
        }

        return $node;
    }

    /**
     * Count the nodes in the queue.
     *
     * @return int<0, max>
     */
    public function count(): int
    {
        $count = 0;
        foreach ($this->queue as $list) {
            $count += count($list);
        }

        /** @var int<0, max> */
        return $count;
    }
}
