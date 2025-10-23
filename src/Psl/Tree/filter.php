<?php

declare(strict_types=1);

namespace Psl\Tree;

use Closure;

/**
 * Filters tree nodes based on a predicate.
 *
 * Removes nodes where predicate returns false.
 * If a parent is removed, its children are also removed.
 *
 * Example:
 *
 *      Tree\filter(
 *          Tree\tree(1, [Tree\leaf(2), Tree\leaf(3)]),
 *          fn($x) => $x > 1
 *      )
 *      => null (root doesn't match)
 *
 *      Tree\filter(
 *          Tree\tree(2, [Tree\leaf(1), Tree\leaf(3)]),
 *          fn($x) => $x > 1
 *      )
 *      => Tree\tree(2, [Tree\tree(3, [])])
 *
 * @template T
 *
 * @param NodeInterface<T>   $node
 * @param (Closure(T): bool) $predicate
 *
 * @return TreeNode<T>|null null if root doesn't match predicate
 */
function filter(NodeInterface $node, Closure $predicate): null|TreeNode
{
    if (!$predicate($node->getValue())) {
        return null;
    }

    $filtered_children = [];
    if ($node instanceof TreeNode) {
        foreach ($node->getChildren() as $child) {
            $filtered = filter($child, $predicate);
            if (null !== $filtered) {
                $filtered_children[] = $filtered;
            }
        }
    }

    return new TreeNode($node->getValue(), $filtered_children);
}
