<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\Unix\Internal\assert_not_windows' => __DIR__ . '/Unix/Internal/assert_not_windows.php',
        'Psl\Unix\connect' => __DIR__ . '/Unix/connect.php',
        'Psl\Unix\listen' => __DIR__ . '/Unix/listen.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
