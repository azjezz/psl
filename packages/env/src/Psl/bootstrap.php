<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\Env\args' => __DIR__ . '/Env/args.php',
        'Psl\Env\current_dir' => __DIR__ . '/Env/current_dir.php',
        'Psl\Env\current_exec' => __DIR__ . '/Env/current_exec.php',
        'Psl\Env\get_var' => __DIR__ . '/Env/get_var.php',
        'Psl\Env\get_vars' => __DIR__ . '/Env/get_vars.php',
        'Psl\Env\join_paths' => __DIR__ . '/Env/join_paths.php',
        'Psl\Env\remove_var' => __DIR__ . '/Env/remove_var.php',
        'Psl\Env\set_current_dir' => __DIR__ . '/Env/set_current_dir.php',
        'Psl\Env\set_var' => __DIR__ . '/Env/set_var.php',
        'Psl\Env\split_paths' => __DIR__ . '/Env/split_paths.php',
        'Psl\Env\temp_dir' => __DIR__ . '/Env/temp_dir.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
