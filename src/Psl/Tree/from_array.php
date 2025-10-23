<?php

declare(strict_types=1);

namespace Psl\Tree;

use Psl\Vec;

/**
 * Creates a tree from a nested array structure.
 *
 * Example:
 *
 *      Tree\from_array([
 *          'value' => 'root',
 *          'children' => [
 *              ['value' => 'child1', 'children' => []],
 *              ['value' => 'child2', 'children' => []],
 *          ],
 *      ])
 *
 * @template T
 *
 * @param array{
 *   value: T,
 *   children?: list<array{
 *     value: T,
 *     children?: list<array{
 *       value: T,
 *       children?: list<array{
 *         value: T,
 *         children?: list<array{
 *           value: T,
 *           children?: list<array>
 *         }>
 *       }>
 *     }>
 *   }>
 * } $array
 *
 * @return TreeNode<T>
 *
 * @pure
 */
function from_array(array $array): TreeNode
{
    /**
     * @var list<array{
     *   value: T,
     *   children?: list<array{
     *     value: T,
     *     children?: list<array{
     *       value: T,
     *       children?: list<array{
     *         value: T,
     *         children?: list<array{
     *           value: T,
     *           children?: list<array>
     *         }>
     *       }>
     *     }>
     *   }>
     * }> $children
     */
    $children = $array['children'] ?? [];

    return new TreeNode($array['value'], Vec\map($children, from_array(...)));
}
