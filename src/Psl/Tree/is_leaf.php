<?php

declare(strict_types=1);

namespace Psl\Tree;

/**
 * Checks if a node is a leaf (has no children).
 *
 * Example:
 *
 *      Tree\is_leaf(Tree\leaf('value'))
 *      => true
 *
 *      Tree\is_leaf(Tree\tree('value', [Tree\leaf('child')]))
 *      => false
 *
 * @template T
 *
 * @param NodeInterface<T> $node
 *
 * @return bool
 *
 * @psalm-assert-if-true LeafNode $node
 *
 * @pure
 */
function is_leaf(NodeInterface $node): bool
{
    return $node instanceof LeafNode;
}
