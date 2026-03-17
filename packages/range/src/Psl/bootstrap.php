<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\Range\between' => __DIR__ . '/Range/between.php',
        'Psl\Range\from' => __DIR__ . '/Range/from.php',
        'Psl\Range\full' => __DIR__ . '/Range/full.php',
        'Psl\Range\to' => __DIR__ . '/Range/to.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
