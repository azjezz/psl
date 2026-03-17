<?php

declare(strict_types=1);

namespace Psl\Tree;

/**
 * Returns a list of all leaf values in the tree.
 *
 * Traversal order is pre-order (depth-first).
 *
 * Example:
 *
 *      Tree\leaves(Tree\tree('root', [
 *          Tree\tree('branch', [Tree\leaf('a'), Tree\leaf('b')]),
 *          Tree\leaf('c'),
 *      ]))
 *      => ['a', 'b', 'c']
 *
 * @template T
 *
 * @param NodeInterface<T> $node
 *
 * @return list<T>
 *
 * @pure
 */
function leaves(NodeInterface $node): array
{
    if (!$node instanceof TreeNode) {
        return [$node->getValue()];
    }

    $children = $node->getChildren();
    if ([] === $children) {
        return [$node->getValue()];
    }

    $result = [];
    foreach ($children as $child) {
        /** @var T $leaf */
        foreach (leaves($child) as $leaf) {
            $result[] = $leaf;
        }
    }

    return $result;
}
