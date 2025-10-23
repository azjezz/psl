<?php

declare(strict_types=1);

namespace Psl\Tree;

use Closure;

/**
 * Checks if any value in the tree matches the predicate.
 *
 * Example:
 *
 *      Tree\any(
 *          Tree\tree(1, [Tree\leaf(2), Tree\leaf(3)]),
 *          fn($x) => $x > 2
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
function any(NodeInterface $node, Closure $predicate): bool
{
    if ($predicate($node->getValue())) {
        return true;
    }

    if ($node instanceof TreeNode) {
        foreach ($node->getChildren() as $child) {
            if (any($child, $predicate)) {
                return true;
            }
        }
    }

    return false;
}
