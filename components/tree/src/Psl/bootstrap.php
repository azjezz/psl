<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\Tree\all' => __DIR__ . '/Tree/all.php',
        'Psl\Tree\any' => __DIR__ . '/Tree/any.php',
        'Psl\Tree\at_index' => __DIR__ . '/Tree/at_index.php',
        'Psl\Tree\contains' => __DIR__ . '/Tree/contains.php',
        'Psl\Tree\count' => __DIR__ . '/Tree/count.php',
        'Psl\Tree\depth' => __DIR__ . '/Tree/depth.php',
        'Psl\Tree\filter' => __DIR__ . '/Tree/filter.php',
        'Psl\Tree\find' => __DIR__ . '/Tree/find.php',
        'Psl\Tree\fold' => __DIR__ . '/Tree/fold.php',
        'Psl\Tree\from_array' => __DIR__ . '/Tree/from_array.php',
        'Psl\Tree\from_list' => __DIR__ . '/Tree/from_list.php',
        'Psl\Tree\is_leaf' => __DIR__ . '/Tree/is_leaf.php',
        'Psl\Tree\leaf' => __DIR__ . '/Tree/leaf.php',
        'Psl\Tree\leaves' => __DIR__ . '/Tree/leaves.php',
        'Psl\Tree\level_order' => __DIR__ . '/Tree/level_order.php',
        'Psl\Tree\map' => __DIR__ . '/Tree/map.php',
        'Psl\Tree\path_to' => __DIR__ . '/Tree/path_to.php',
        'Psl\Tree\post_order' => __DIR__ . '/Tree/post_order.php',
        'Psl\Tree\pre_order' => __DIR__ . '/Tree/pre_order.php',
        'Psl\Tree\reduce' => __DIR__ . '/Tree/reduce.php',
        'Psl\Tree\to_array' => __DIR__ . '/Tree/to_array.php',
        'Psl\Tree\to_index' => __DIR__ . '/Tree/to_index.php',
        'Psl\Tree\traverse' => __DIR__ . '/Tree/traverse.php',
        'Psl\Tree\tree' => __DIR__ . '/Tree/tree.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
