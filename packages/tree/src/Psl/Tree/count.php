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
 * @return int<1, max>
 *
 * @pure
 *
 * @api
 */
function count<T>(NodeInterface<T> $node): int
{
    $total = 1;
    if ($node instanceof TreeNode) {
        foreach ($node->getChildren() as $child) {
            $total += namespace\count::<T>($child);
        }
    }

    return $total;
}
