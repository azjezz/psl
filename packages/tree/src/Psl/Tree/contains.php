<?php

declare(strict_types=1);

namespace Psl\Tree;

/**
 * Checks if the tree contains the given value (strict comparison).
 *
 * Example:
 *
 *      Tree\contains(
 *          Tree\tree(1, [Tree\leaf(2), Tree\leaf(3)]),
 *          2
 *      )
 *      => true
 *
 * @template T
 *
 * @param NodeInterface<T> $tree
 * @param T $value
 *
 * @return bool
 *
 * @pure
 */
function contains(NodeInterface $tree, mixed $value): bool
{
    return any($tree, static fn(mixed $v): bool => $v === $value);
}
