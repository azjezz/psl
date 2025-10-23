<?php

declare(strict_types=1);

namespace Psl\Tree;

use Closure;

/**
 * Checks if all values in the tree match the predicate.
 *
 * Example:
 *
 *      Tree\all(
 *          Tree\tree(1, [Tree\leaf(2), Tree\leaf(3)]),
 *          fn($x) => $x > 0
 *      )
 *      => true
 *
 * @template T
 *
 * @param NodeInterface<T>   $node
 * @param (Closure(T): bool) $predicate
 *
 * @return bool
 */
function all(NodeInterface $node, Closure $predicate): bool
{
    if (!$predicate($node->getValue())) {
        return false;
    }

    if ($node instanceof TreeNode) {
        foreach ($node->getChildren() as $child) {
            if (!all($child, $predicate)) {
                return false;
            }
        }
    }

    return true;
}
