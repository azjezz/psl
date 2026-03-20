<?php

declare(strict_types=1);

namespace Psl\Tree;

use function max;

/**
 * Returns the maximum depth of the tree.
 *
 * The depth is the number of edges from the root to the deepest leaf.
 * A leaf node has depth 0, a node with children has depth 1 + max(child depths).
 *
 * Example:
 *
 *      Tree\depth(Tree\leaf('value'))
 *      => 0
 *
 *      Tree\depth(Tree\tree('root', [Tree\leaf('child')]))
 *      => 1
 *
 * @template T
 *
 * @param NodeInterface<T> $tree
 *
 * @return int<0, max>
 *
 * @pure
 */
function depth(NodeInterface $tree): int
{
    if (!$tree instanceof TreeNode) {
        return 0;
    }

    $children = $tree->getChildren();
    if ([] === $children) {
        return 0;
    }

    $childDepths = [];
    foreach ($children as $child) {
        $childDepths[] = namespace\depth($child);
    }

    return 1 + max($childDepths);
}
