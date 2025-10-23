<?php

declare(strict_types=1);

namespace Psl\Tree;

use Closure;
use Psl\Vec;

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
 * @param Closure(TItem): TId $get_id Function to extract the ID from an item
 * @param Closure(TItem): TId|null $get_parent_id Function to extract the parent ID (null for root)
 * @param Closure(TItem): TValue $get_value Function to extract/transform the value to store in the node
 *
 * @return NodeInterface<TValue>
 *
 * @throws Exception\NoRootNodeException if no root item found (item with null parent_id)
 * @throws Exception\MultipleRootNodesException if multiple root items found
 * @throws Exception\OrphanedNodeException if parent_id references non-existent item
 *
 * @pure
 */
function from_list(array $items, Closure $get_id, Closure $get_parent_id, Closure $get_value): NodeInterface
{
    // Group items by parent ID (manual grouping to handle null keys)
    $by_parent = [];
    $roots = [];
    foreach ($items as $item) {
        $parent_id = $get_parent_id($item);
        if (null === $parent_id) {
            $roots[] = $item;

            continue;
        }

        $by_parent[$parent_id] ??= [];
        $by_parent[$parent_id][] = $item;
    }

    $roots_length = count($roots);
    if ($roots_length !== 1) {
        if ($roots_length > 1) {
            throw new Exception\MultipleRootNodesException();
        }

        throw new Exception\NoRootNodeException();
    }

    $root_item = $roots[0];

    // Create a map of id => item for validation
    $items_by_id = [];
    foreach ($items as $item) {
        $items_by_id[$get_id($item)] = $item;
    }

    // Validate all parent_id references before building
    foreach ($items as $item) {
        $parent_id = $get_parent_id($item);
        if (null !== $parent_id && !isset($items_by_id[$parent_id])) {
            $item_id = $get_id($item);
            throw new Exception\OrphanedNodeException($item_id, $parent_id);
        }
    }

    // Build tree recursively
    $build =
        /**
         * @param TItem $item
         *
         * @returns NodeInterface<TValue>
         */
        static function (mixed $item) use ($by_parent, $get_id, $get_value, &$build): NodeInterface {
            $item_id = $get_id($item);
            $value = $get_value($item);
            $children_items = $by_parent[$item_id] ?? [];

            if ([] === $children_items) {
                return leaf($value);
            }

            $children = Vec\map($children_items, $build(...));

            return tree($value, $children);
        };

    return $build($root_item);
}
