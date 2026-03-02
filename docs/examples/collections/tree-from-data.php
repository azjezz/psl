<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Tree;

// From a nested array
$tree = Tree\from_array([
    'value' => 'root',
    'children' => [
        ['value' => 'child1', 'children' => []],
        ['value' => 'child2', 'children' => []],
    ],
]);

// From database records with parent_id relationships
$records = [
    ['id' => 1, 'name' => 'Root', 'parent_id' => null],
    ['id' => 2, 'name' => 'Child A', 'parent_id' => 1],
    ['id' => 3, 'name' => 'Child B', 'parent_id' => 1],
    ['id' => 4, 'name' => 'Grandchild', 'parent_id' => 2],
];

$tree = Tree\from_list(
    $records,
    fn($record) => $record['id'],
    fn($record) => $record['parent_id'],
    fn($record) => $record['name'],
);
