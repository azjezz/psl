<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\Option\from_nullable' => __DIR__ . '/Option/from_nullable.php',
        'Psl\Option\none' => __DIR__ . '/Option/none.php',
        'Psl\Option\some' => __DIR__ . '/Option/some.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
