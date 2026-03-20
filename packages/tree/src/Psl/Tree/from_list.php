<?php

declare(strict_types=1);

namespace Psl\Tree;

use Closure;

use function array_map;
use function count;

/**
 * Builds a tree from a flat list of items with parent references.
 *
 * This is useful for building trees from database records that use parent_id relationships.
 *
 * Example:
 *
 *      $records = [
 *          ['id' => 1, 'name' => 'Root', 'parent_id' => null],
 *          ['id' => 2, 'name' => 'Child A', 'parent_id' => 1],
 *          ['id' => 3, 'name' => 'Child B', 'parent_id' => 1],
 *          ['id' => 4, 'name' => 'Grandchild', 'parent_id' => 2],
 *      ];
 *
 *      $tree = Tree\from_list(
 *          $records,
 *          fn($record) => $record['id'],        // Get node ID
 *          fn($record) => $record['parent_id'], // Get parent ID (null for root)
 *          fn($record) => $record               // Extract value
 *      );
 *
 *      // Or transform the value:
 *      $tree = Tree\from_list(
 *          $records,
 *          fn($r) => $r['id'],
 *          fn($r) => $r['parent_id'],
 *          fn($r) => $r['name']  // Store just the name
 *      );
 *
 * @template TItem
 * @template TId of array-key
 * @template TValue
 *
 * @param non-empty-list<TItem> $items The flat list of items
 * @param Closure(TItem): TId $getId Function to extract the ID from an item
 * @param Closure(TItem): (TId|null) $getParentId Function to extract the parent ID (null for root)
 * @param Closure(TItem): TValue $getValue Function to extract/transform the value to store in the node
 *
 * @return NodeInterface<TValue>
 *
 * @throws Exception\NoRootNodeException if no root item found (item with null parent_id)
 * @throws Exception\MultipleRootNodesException if multiple root items found
 * @throws Exception\OrphanedNodeException if parent_id references non-existent item
 *
 * @pure
 */
function from_list(array $items, Closure $getId, Closure $getParentId, Closure $getValue): NodeInterface
{
    // Group items by parent ID (manual grouping to handle null keys)
    $byParent = [];
    $roots = [];
    foreach ($items as $item) {
        $parentId = $getParentId($item);
        if (null === $parentId) {
            $roots[] = $item;

            continue;
        }

        $byParent[$parentId] ??= [];
        $byParent[$parentId][] = $item;
    }

    $rootsLength = count($roots);
    if ($rootsLength !== 1) {
        if ($rootsLength > 1) {
            throw new Exception\MultipleRootNodesException();
        }

        throw new Exception\NoRootNodeException();
    }

    $rootItem = $roots[0];

    // Create a map of id => item for validation
    $itemsById = [];
    foreach ($items as $item) {
        $itemsById[$getId($item)] = $item;
    }

    // Validate all parent_id references before building
    foreach ($items as $item) {
        $parentId = $getParentId($item);
        if (null !== $parentId && !isset($itemsById[$parentId])) {
            $itemId = $getId($item);
            throw new Exception\OrphanedNodeException($itemId, $parentId);
        }
    }

    // Build tree recursively
    $build =
        /**
         * @param TItem $item
         *
         * @returns NodeInterface<TValue>
         */
        static function (mixed $item) use ($byParent, $getId, $getValue, &$build): NodeInterface {
            $itemId = $getId($item);
            $value = $getValue($item);
            $childrenItems = $byParent[$itemId] ?? [];

            if ([] === $childrenItems) {
                return namespace\leaf($value);
            }

            $children = array_map($build(...), $childrenItems);

            return namespace\tree($value, $children);
        };

    return $build($rootItem);
}
