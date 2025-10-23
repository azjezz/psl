<?php

declare(strict_types=1);

namespace Psl\Tree;

use Psl\Vec;

/**
 * Gets the value at the specified index path (list of child indices).
 *
 * The index path is a list of 0-based child indices to follow from the root.
 * An empty index path returns the root value.
 *
 * Example:
 *
 *      Tree\at_index(
 *          Tree\tree('a', [
 *              Tree\tree('b', [Tree\leaf('c')]),
 *              Tree\leaf('d'),
 *          ]),
 *          [0, 0]
 *      )
 *      => 'c'
 *
 *      Tree\at_index($tree, [])
 *      => 'a' (root value)
 *
 * @template T
 *
 * @param NodeInterface<T>  $node
 * @param list<int<0, max>> $index_path
 *
 * @return T|null null if the index path is invalid
 *
 * @pure
 */
function at_index(NodeInterface $node, array $index_path): mixed
{
    if ([] === $index_path) {
        return $node->getValue();
    }

    if ($node instanceof LeafNode) {
        return null;
    }

    $index = $index_path[0];
    $children = $node->getChildren();
    $node = $children[$index] ?? null;
    if (null === $node) {
        return null;
    }

    /** @var T */
    return at_index($node, Vec\slice($index_path, 1));
}
