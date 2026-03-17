<?php

declare(strict_types=1);

namespace Psl\Tree;

/**
 * Counts total number of nodes in the tree.
 *
 * Example:
 *
 *      Tree\count(Tree\tree('root', [
 *          Tree\leaf('child1'),
 *          Tree\leaf('child2'),
 *      ]))
 *      => 3
 *
 * @template T
 *
 * @param NodeInterface<T> $node
 *
 * @return int<1, max>
 *
 * @pure
 */
function count(NodeInterface $node): int
{
    $total = 1;
    if ($node instanceof TreeNode) {
        foreach ($node->getChildren() as $child) {
            $total += count($child);
        }
    }

    /** @var int<1, max> */
    return $total;
}
