<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\Shell\execute' => __DIR__ . '/Shell/execute.php',
        'Psl\Shell\stream_unpack' => __DIR__ . '/Shell/stream_unpack.php',
        'Psl\Shell\unpack' => __DIR__ . '/Shell/unpack.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
