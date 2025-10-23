<?php

declare(strict_types=1);

namespace Psl\Tree;

use Closure;
use Psl\Vec;

/**
 * Applies a mapping function to all values in the tree.
 *
 * Preserves tree structure.
 *
 * Example:
 *
 *      Tree\map(
 *          Tree\tree(1, [Tree\leaf(2), Tree\leaf(3)]),
 *          fn($x) => $x * 2
 *      )
 *      => Tree\tree(2, [Tree\leaf(4), Tree\leaf(6)])
 *
 * @template T
 * @template Tu
 *
 * @param NodeInterface<T> $node
 * @param (Closure(T): Tu) $function
 *
 * @return NodeInterface<Tu>
 */
function map(NodeInterface $node, Closure $function): NodeInterface
{
    if (!$node instanceof TreeNode) {
        return new LeafNode($function($node->getValue()));
    }

    return new TreeNode(
        $function($node->getValue()),
        Vec\map($node->getChildren(), static fn(NodeInterface $child): NodeInterface => map($child, $function)),
    );
}
