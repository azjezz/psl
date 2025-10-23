<?php

declare(strict_types=1);

namespace Psl\Tree;

/**
 * Creates a leaf node (node with no children).
 *
 * Example:
 *
 *      Tree\leaf('value')
 *
 * @template T
 *
 * @param T $value
 *
 * @return LeafNode<T>
 *
 * @pure
 */
function leaf(mixed $value): LeafNode
{
    return new LeafNode($value);
}
