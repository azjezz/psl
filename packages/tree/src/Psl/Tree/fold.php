<?php

declare(strict_types=1);

namespace Psl\Tree;

use Closure;

use function array_map;

/**
 * Folds tree from leaves to root (post-order).
 *
 * The function receives the current value and a list of folded children results.
 *
 * Example:
 *
 *      Tree\fold(
 *          Tree\tree('root', [Tree\leaf('a'), Tree\leaf('b')]),
 *          fn($value, $children) => $value . '(' . implode(',', $children) . ')'
 *      )
 *      => 'root(a,b)'
 *
 * @param NodeInterface<T> $tree
 * @param (Closure(T, list<Ta>): Ta) $function
 *
 * @return Ta
 *
 * @api
 */
function fold<T = mixed, Ta = mixed>(NodeInterface<T> $tree, Closure $function): Ta
{
    if (!$tree instanceof TreeNode) {
        return $function($tree->getValue(), []);
    }

    $foldedChildren = array_map(static fn(NodeInterface $child): mixed => namespace\fold(
        $child,
        $function,
    ), $tree->getChildren());

    return $function($tree->getValue(), $foldedChildren);
}
