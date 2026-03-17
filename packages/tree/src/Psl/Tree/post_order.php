<?php

declare(strict_types=1);

namespace Psl\Tree;

/**
 * Performs post-order traversal (children, then root).
 *
 * Returns a list of all values in post-order (depth-first).
 *
 * Example:
 *
 *      Tree\post_order(Tree\tree('a', [
 *          Tree\tree('b', [Tree\leaf('c')]),
 *          Tree\leaf('d'),
 *      ]))
 *      => ['c', 'b', 'd', 'a']
 *
 * @template T
 *
 * @param NodeInterface<T> $tree
 *
 * @return list<T>
 *
 * @pure
 */
function post_order(NodeInterface $tree): array
{
    if (!$tree instanceof TreeNode) {
        return [$tree->getValue()];
    }

    $result = [];
    foreach ($tree->getChildren() as $child) {
        /** @var T $value */
        foreach (post_order($child) as $value) {
            $result[] = $value;
        }
    }

    $result[] = $tree->getValue();

    return $result;
}
