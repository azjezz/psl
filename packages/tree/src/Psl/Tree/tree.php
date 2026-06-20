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
 * @param list<NodeInterface<T>> $children
 *
 * @pure
 *
 * @api
 */
function tree<T>(T $value, array $children = []): TreeNode<T>
{
    return new TreeNode::<T>($value, $children);
}
