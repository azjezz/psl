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
 * @return bool
 *
 * @pure
 *
 * @api
 */
function contains<T>(NodeInterface<T> $tree, T $value): bool
{
    return namespace\any::<T>($tree, static fn(mixed $v): bool => $v === $value);
}
