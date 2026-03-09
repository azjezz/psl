<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Math;
use Psl\Tree;

$tree = Tree\tree(1, [Tree\leaf(2), Tree\leaf(3)]);

// Map: apply a function to every node value
$doubled = Tree\map($tree, fn(int $x): int => $x * 2);
// Result: tree(2, [leaf(4), leaf(6)])

// Filter: keep only nodes matching a predicate
$filtered = Tree\filter($tree, fn(int $x): bool => $x >= 2);
// Result: tree(2, [leaf(3)]) -- root must match or null is returned

// Reduce: collapse the tree to a single value (pre-order)
$sum = Tree\reduce($tree, fn(int $acc, int $x): int => $acc + $x, 0);
// Result: 6

// Fold: post-order fold with access to children results
$result = Tree\fold(
    $tree,
    /**
     * @param int $value
     * @param list<int> $children
     * @return int
     */
    fn(int $value, array $children): int => $value + Math\sum($children),
);

// Result: 6
