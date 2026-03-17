<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\File\Internal\open' => __DIR__ . '/File/Internal/open.php',
        'Psl\File\Internal\suppress' => __DIR__ . '/File/Internal/suppress.php',
        'Psl\File\open_read_only' => __DIR__ . '/File/open_read_only.php',
        'Psl\File\open_read_write' => __DIR__ . '/File/open_read_write.php',
        'Psl\File\open_write_only' => __DIR__ . '/File/open_write_only.php',
        'Psl\File\read' => __DIR__ . '/File/read.php',
        'Psl\File\write' => __DIR__ . '/File/write.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
