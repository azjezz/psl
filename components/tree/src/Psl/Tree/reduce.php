<?php

declare(strict_types=1);

namespace Psl\Tree;

use Closure;

/**
 * Reduces tree to a single value using pre-order traversal.
 *
 * Example:
 *
 *      Tree\reduce(
 *          Tree\tree(1, [Tree\leaf(2), Tree\leaf(3)]),
 *          fn($acc, $x) => $acc + $x,
 *          0
 *      )
 *      => 6
 *
 * @template T
 * @template Ta
 *
 * @param NodeInterface<T> $tree
 * @param (Closure(Ta, T): Ta) $function
 * @param Ta $initial
 *
 * @return Ta
 */
function reduce(NodeInterface $tree, Closure $function, mixed $initial): mixed
{
    $accumulator = $function($initial, $tree->getValue());

    if (!$tree instanceof TreeNode) {
        return $accumulator;
    }

    foreach ($tree->getChildren() as $child) {
        $accumulator = reduce($child, $function, $accumulator);
    }

    return $accumulator;
}
