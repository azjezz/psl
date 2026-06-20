<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Tree;

$tree = Tree\tree::<string>('root', [
    Tree\leaf::<string>('child1'),
    Tree\leaf::<string>('child2'),
    Tree\tree::<string>('child3', [
        Tree\leaf::<string>('grandchild1'),
    ]),
]);
