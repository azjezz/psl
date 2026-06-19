<?php

declare(strict_types=1);

namespace Psl\Tree;

use function array_map;

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
 *
 * @api
 */
function from_array<T = mixed>(array $array): TreeNode<T>
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

    return new TreeNode($array['value'], array_map(from_array(...), $children));
}
