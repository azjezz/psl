<?php

declare(strict_types=1);

namespace Psl\Tree;

use Psl\Vec;

/**
 * Converts a tree to a nested array structure.
 *
 * Example:
 *
 *      Tree\to_array(Tree\tree('root', [
 *          Tree\leaf('child1'),
 *          Tree\leaf('child2'),
 *      ]))
 *      => [
 *          'value' => 'root',
 *          'children' => [
 *              ['value' => 'child1', 'children' => []],
 *              ['value' => 'child2', 'children' => []],
 *          ],
 *      ]
 *
 * @template T
 *
 * @param NodeInterface<T> $tree
 *
 * @return array{
 *   value: T,
 *   children: list<array{
 *     value: T,
 *     children: list<array{
 *       value: T,
 *       children: list<array{
 *         value: T,
 *         children: list<array{
 *             value: T,
 *             children: list<array{
 *                 value: T,
 *                 children: list<array>
 *             }>
 *         }>
 *       }>
 *     }>
 *   }>
 * }
 *
 * @pure
 */
function to_array(NodeInterface $tree): array
{
    if ($tree instanceof LeafNode) {
        return [
            'value' => $tree->getValue(),
            'children' => [],
        ];
    }

    return [
        'value' => $tree->getValue(),
        'children' => Vec\map($tree->getChildren(), to_array(...)),
    ];
}
