<?php

declare(strict_types=1);

namespace Psl\Tree;

use Closure;

/**
 * Finds the index path from root to the first node matching the predicate.
 *
 * Returns a list of child indices from root to the target node.
 *
 * Example:
 *
 *      Tree\to_index(
 *          Tree\tree('a', [
 *              Tree\tree('b', [Tree\leaf('c')]),
 *              Tree\leaf('d'),
 *          ]),
 *          fn($x) => $x === 'c'
 *      )
 *      => [0, 0]
 *
 *      Tree\to_index($tree, fn($x) => $x === 'd')
 *      => [1]
 *
 * @template T
 *
 * @param NodeInterface<T> $tree
 * @param (Closure(T): bool) $predicate
 *
 * @return list<int<0, max>>|null null if no node matches the predicate
 *
 * @pure
 */
function to_index(NodeInterface $tree, Closure $predicate): null|array
{
    $value = $tree->getValue();

    if ($predicate($value)) {
        return [];
    }

    if (!$tree instanceof TreeNode) {
        return null;
    }

    foreach ($tree->getChildren() as $index => $child) {
        $child_path = to_index($child, $predicate);
        if (null !== $child_path) {
            return [$index, ...$child_path];
        }
    }

    return null;
}
