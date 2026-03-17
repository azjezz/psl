<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\Fun\after' => __DIR__ . '/Fun/after.php',
        'Psl\Fun\identity' => __DIR__ . '/Fun/identity.php',
        'Psl\Fun\lazy' => __DIR__ . '/Fun/lazy.php',
        'Psl\Fun\pipe' => __DIR__ . '/Fun/pipe.php',
        'Psl\Fun\rethrow' => __DIR__ . '/Fun/rethrow.php',
        'Psl\Fun\tap' => __DIR__ . '/Fun/tap.php',
        'Psl\Fun\when' => __DIR__ . '/Fun/when.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
