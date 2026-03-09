<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Str;
use Psl\Tree;

$tree = Tree\tree('a', [
    Tree\tree('b', [Tree\leaf('c')]),
    Tree\leaf('d'),
]);

// Find the path from root to a matching node
$path = Tree\path_to($tree, fn($x) => $x === 'c');
// Result: ['a', 'b', 'c']

// Navigate by index path
Tree\at_index($tree, [0, 0]); // 'c'
Tree\at_index($tree, [1]); // 'd'

// Search for values
Tree\find($tree, fn($x) => $x === 'c'); // 'c'
Tree\contains($tree, 'd'); // true
Tree\any($tree, fn($x) => $x === 'b'); // true
Tree\all($tree, fn($x) => Str\length($x) === 1); // true
