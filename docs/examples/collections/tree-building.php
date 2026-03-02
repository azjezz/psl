<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Tree;

$tree = Tree\tree('root', [
    Tree\leaf('child1'),
    Tree\leaf('child2'),
    Tree\tree('child3', [
        Tree\leaf('grandchild1'),
    ]),
]);
