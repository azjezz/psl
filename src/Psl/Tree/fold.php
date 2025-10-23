<?php

declare(strict_types=1);

namespace Psl\Tree;

use Closure;
use Psl\Vec;

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
 * @template T
 * @template Ta
 *
 * @param NodeInterface<T> $tree
 * @param (Closure(T, list<Ta>): Ta) $function
 *
 * @return Ta
 */
function fold(NodeInterface $tree, Closure $function): mixed
{
    if (!$tree instanceof TreeNode) {
        return $function($tree->getValue(), []);
    }

    $folded_children = Vec\map($tree->getChildren(), static fn(NodeInterface $child): mixed => fold($child, $function));

    return $function($tree->getValue(), $folded_children);
}
