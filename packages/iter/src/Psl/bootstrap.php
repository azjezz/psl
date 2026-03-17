<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\Iter\all' => __DIR__ . '/Iter/all.php',
        'Psl\Iter\any' => __DIR__ . '/Iter/any.php',
        'Psl\Iter\apply' => __DIR__ . '/Iter/apply.php',
        'Psl\Iter\contains' => __DIR__ . '/Iter/contains.php',
        'Psl\Iter\contains_key' => __DIR__ . '/Iter/contains_key.php',
        'Psl\Iter\count' => __DIR__ . '/Iter/count.php',
        'Psl\Iter\first' => __DIR__ . '/Iter/first.php',
        'Psl\Iter\first_key' => __DIR__ . '/Iter/first_key.php',
        'Psl\Iter\first_key_opt' => __DIR__ . '/Iter/first_key_opt.php',
        'Psl\Iter\first_opt' => __DIR__ . '/Iter/first_opt.php',
        'Psl\Iter\is_empty' => __DIR__ . '/Iter/is_empty.php',
        'Psl\Iter\last' => __DIR__ . '/Iter/last.php',
        'Psl\Iter\last_key' => __DIR__ . '/Iter/last_key.php',
        'Psl\Iter\last_key_opt' => __DIR__ . '/Iter/last_key_opt.php',
        'Psl\Iter\last_opt' => __DIR__ . '/Iter/last_opt.php',
        'Psl\Iter\random' => __DIR__ . '/Iter/random.php',
        'Psl\Iter\reduce' => __DIR__ . '/Iter/reduce.php',
        'Psl\Iter\reduce_keys' => __DIR__ . '/Iter/reduce_keys.php',
        'Psl\Iter\reduce_with_keys' => __DIR__ . '/Iter/reduce_with_keys.php',
        'Psl\Iter\rewindable' => __DIR__ . '/Iter/rewindable.php',
        'Psl\Iter\search' => __DIR__ . '/Iter/search.php',
        'Psl\Iter\search_opt' => __DIR__ . '/Iter/search_opt.php',
        'Psl\Iter\search_with_keys' => __DIR__ . '/Iter/search_with_keys.php',
        'Psl\Iter\search_with_keys_opt' => __DIR__ . '/Iter/search_with_keys_opt.php',
        'Psl\Iter\to_iterator' => __DIR__ . '/Iter/to_iterator.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
