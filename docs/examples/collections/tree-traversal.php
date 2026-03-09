<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Tree;

$tree = Tree\tree(1, [Tree\tree(2, [Tree\leaf(3)]), Tree\leaf(4)]);

Tree\pre_order($tree); // [1, 2, 3, 4] -- root first, then children
Tree\post_order($tree); // [3, 2, 4, 1] -- children first, then root
Tree\level_order($tree); // [1, 2, 4, 3] -- breadth-first
