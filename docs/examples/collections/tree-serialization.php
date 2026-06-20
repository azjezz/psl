<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Iter;
use Psl\Str;
use Psl\Tree;

$tree = Tree\tree::<string>('root', [Tree\leaf::<string>('a'), Tree\leaf::<string>('b')]);

// Build a custom string representation
$result = Tree\traverse::<string, string>(
    $tree,
    /**
     * @param non-empty-string $value
     * @param Closure(): list<non-empty-string> $traverse
     *
     * @return non-empty-string
     */
    function (string $value, Closure $traverse): string {
        $children = $traverse();
        return Iter\is_empty::<string>($children) ? $value : $value . '(' . Str\join($children, ',') . ')';
    },
);

// Result: 'root(a,b)'
