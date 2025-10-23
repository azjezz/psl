<?php

declare(strict_types=1);

namespace Psl\Tree;

use Closure;

/**
 * Finds the first value matching predicate (pre-order traversal).
 *
 * Example:
 *
 *      Tree\find(
 *          Tree\tree(1, [Tree\leaf(2), Tree\leaf(3)]),
 *          fn($x) => $x > 1
 *      )
 *      => 2
 *
 * @template T
 *
 * @param NodeInterface<T> $tree
 * @param (Closure(T): bool) $predicate
 *
 * @return T|null
 */
function find(NodeInterface $tree, Closure $predicate): mixed
{
    $value = $tree->getValue();
    if ($predicate($value)) {
        return $value;
    }

    if (!$tree instanceof TreeNode) {
        return null;
    }

    foreach ($tree->getChildren() as $child) {
        /** @var T|null $result */
        $result = find($child, $predicate);
        if (null !== $result) {
            return $result;
        }
    }

    return null;
}
