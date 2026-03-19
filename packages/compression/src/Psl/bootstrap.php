<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\Compression\compress' => __DIR__ . '/Compression/compress.php',
        'Psl\Compression\decompress' => __DIR__ . '/Compression/decompress.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
