<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Tree;

$tree = Tree\tree::<int>(1, [Tree\tree::<int>(2, [Tree\leaf::<int>(3)]), Tree\leaf::<int>(4)]);

Tree\pre_order::<int>($tree); // [1, 2, 3, 4] -- root first, then children
Tree\post_order::<int>($tree); // [3, 2, 4, 1] -- children first, then root
Tree\level_order::<int>($tree); // [1, 2, 4, 3] -- breadth-first
