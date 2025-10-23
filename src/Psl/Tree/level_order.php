<?php

declare(strict_types=1);

namespace Psl\Tree;

use Psl\DataStructure\Queue;

/**
 * Performs level-order (breadth-first) traversal.
 *
 * Returns a list of all values in level-order (breadth-first).
 *
 * Example:
 *
 *      Tree\level_order(Tree\tree('a', [
 *          Tree\tree('b', [Tree\leaf('c')]),
 *          Tree\leaf('d'),
 *      ]))
 *      => ['a', 'b', 'd', 'c']
 *
 * @template T
 *
 * @param NodeInterface<T> $root_node
 *
 * @return list<T>
 *
 * @pure
 */
function level_order(NodeInterface $root_node): array
{
    $result = [];
    /** @var Queue<NodeInterface<T>> $queue */
    $queue = new Queue();
    $queue->enqueue($root_node);

    while ($queue->count() !== 0) {
        $node = $queue->dequeue();
        $result[] = $node->getValue();

        if ($node instanceof TreeNode) {
            foreach ($node->getChildren() as $child) {
                $queue->enqueue($child);
            }
        }
    }

    return $result;
}
