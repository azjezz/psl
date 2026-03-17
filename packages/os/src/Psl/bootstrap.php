<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\OS\family' => __DIR__ . '/OS/family.php',
        'Psl\OS\is_darwin' => __DIR__ . '/OS/is_darwin.php',
        'Psl\OS\is_windows' => __DIR__ . '/OS/is_windows.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
