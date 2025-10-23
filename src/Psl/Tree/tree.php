<?php

declare(strict_types=1);

namespace Psl\Tree;

/**
 * Creates a tree node with the given value and children.
 *
 * Example:
 *
 *      Tree\tree('root', [
 *          Tree\tree('child1'),
 *          Tree\leaf('child2'),
 *      ])
 *
 * @template T
 *
 * @param T $value
 * @param list<NodeInterface<T>> $children
 *
 * @return TreeNode<T>
 *
 * @pure
 */
function tree(mixed $value, array $children = []): TreeNode
{
    return new TreeNode($value, $children);
}
