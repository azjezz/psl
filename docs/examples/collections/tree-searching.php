<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Str;
use Psl\Tree;

$tree = Tree\tree::<string>('a', [
    Tree\tree::<string>('b', [Tree\leaf::<string>('c')]),
    Tree\leaf::<string>('d'),
]);

// Find the path from root to a matching node
$path = Tree\path_to::<string>($tree, fn($x) => $x === 'c');
// Result: ['a', 'b', 'c']

// Navigate by index path
Tree\at_index::<string>($tree, [0, 0]); // 'c'
Tree\at_index::<string>($tree, [1]); // 'd'

// Search for values
Tree\find::<string>($tree, fn($x) => $x === 'c'); // 'c'
Tree\contains::<string>($tree, 'd'); // true
Tree\any::<string>($tree, fn($x) => $x === 'b'); // true
Tree\all::<string>($tree, fn($x) => Str\length($x) === 1); // true
