<?php

declare(strict_types=1);

namespace Psl\Tree;

/**
 * Performs pre-order traversal (root, then children).
 *
 * Returns a list of all values in pre-order (depth-first).
 *
 * Example:
 *
 *      Tree\pre_order(Tree\tree('a', [
 *          Tree\tree('b', [Tree\leaf('c')]),
 *          Tree\leaf('d'),
 *      ]))
 *      => ['a', 'b', 'c', 'd']
 *
 * @template T
 *
 * @param NodeInterface<T> $tree
 *
 * @return list<T>
 *
 * @pure
 */
function pre_order(NodeInterface $tree): array
{
    $result = [$tree->getValue()];
    if (!$tree instanceof TreeNode) {
        return $result;
    }

    foreach ($tree->getChildren() as $child) {
        /** @var T $value */
        foreach (pre_order($child) as $value) {
            $result[] = $value;
        }
    }

    return $result;
}
