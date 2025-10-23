<?php

declare(strict_types=1);

namespace Psl\Tree;

use Closure;

/**
 * Finds the path from root to the first node matching the predicate.
 *
 * Returns a list of values from root to the target node (inclusive).
 *
 * Example:
 *
 *      Tree\path_to(
 *          Tree\tree('a', [
 *              Tree\tree('b', [Tree\leaf('c')]),
 *              Tree\leaf('d'),
 *          ]),
 *          fn($x) => $x === 'c'
 *      )
 *      => ['a', 'b', 'c']
 *
 * @template T
 *
 * @param NodeInterface<T> $tree
 * @param (Closure(T): bool) $predicate
 *
 * @return list<T>|null null if no node matches the predicate
 */
function path_to(NodeInterface $tree, Closure $predicate): null|array
{
    $value = $tree->getValue();

    if ($predicate($value)) {
        return [$value];
    }

    if (!$tree instanceof TreeNode) {
        return null;
    }

    foreach ($tree->getChildren() as $child) {
        $child_path = path_to($child, $predicate);
        if (null !== $child_path) {
            return [$value, ...$child_path];
        }
    }

    return null;
}
