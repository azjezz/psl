<?php

declare(strict_types=1);

namespace Psl\Tree;

use Closure;
use Psl\Vec;

/**
 * Traverses the tree and transforms each node using a custom function.
 *
 * This allows you to serialize the tree structure in a custom format,
 * with control over how children are represented (property names, structure, etc).
 *
 * The transform function receives:
 * - The current node's value
 * - A callable that, when invoked, returns the transformed children
 *
 * Example - Custom JSON structure with specific children property name:
 *
 *      Tree\traverse(
 *          Tree\tree(['id' => 1, 'label' => 'A'], [
 *              Tree\leaf(['id' => 2, 'label' => 'B']),
 *              Tree\leaf(['id' => 3, 'label' => 'C'])
 *          ]),
 *          fn($value, $traverse) => [
 *              'id' => $value['id'],
 *              'label' => $value['label'],
 *              'customChildrenProp' => $traverse()
 *          ]
 *      )
 *      => [
 *          'id' => 1,
 *          'label' => 'A',
 *          'customChildrenProp' => [
 *              ['id' => 2, 'label' => 'B', 'customChildrenProp' => []],
 *              ['id' => 3, 'label' => 'C', 'customChildrenProp' => []]
 *          ]
 *      ]
 *
 * Example - Conditional children rendering:
 *
 *      Tree\traverse(
 *          $tree,
 *          fn($value, $traverse) => [
 *              'name' => $value,
 *              'hasChildren' => $tree instanceof TreeNode,
 *              'children' => $tree instanceof TreeNode ? $traverse() : null
 *          ]
 *      )
 *
 * @template TValue
 * @template TResult
 *
 * @param NodeInterface<TValue> $tree
 * @param (Closure(TValue, (Closure(): list<TResult>)): TResult) $transform
 *
 * @return TResult
 *
 * @pure
 */
function traverse(NodeInterface $tree, Closure $transform): mixed
{
    $value = $tree->getValue();

    $get_children = static function () use ($tree, $transform): array {
        if (!$tree instanceof TreeNode) {
            return [];
        }

        return Vec\map($tree->getChildren(), static fn(NodeInterface $child): mixed => traverse($child, $transform));
    };

    return $transform($value, $get_children);
}
