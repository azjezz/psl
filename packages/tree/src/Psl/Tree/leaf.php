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
 * @param T $value
 *
 * @return LeafNode<T>
 *
 * @pure
 *
 * @api
 */
function leaf<T = mixed>(T $value): LeafNode<T>
{
    return new LeafNode($value);
}
