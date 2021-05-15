<?php

declare(strict_types=1);

namespace Psl\DataStructure;

/**
 * @template T
 *
 * @extends QueueInterface<T>
 */
interface PriorityQueueInterface extends QueueInterface
{
    /**
     * Adds a node to the queue.
     *
     * @param T $node
     */
    public function enqueue(mixed $node, int $priority = 0): void;
}
