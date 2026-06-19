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
 * @param NodeInterface<T> $tree
 * @param (Closure(Ta, T): Ta) $function
 * @param Ta $initial
 *
 * @return Ta
 *
 * @api
 */
function reduce<T = mixed, Ta = mixed>(NodeInterface<T> $tree, Closure $function, Ta $initial): Ta
{
    $accumulator = $function($initial, $tree->getValue());

    if (!$tree instanceof TreeNode) {
        return $accumulator;
    }

    foreach ($tree->getChildren() as $child) {
        $accumulator = namespace\reduce($child, $function, $accumulator);
    }

    return $accumulator;
}
