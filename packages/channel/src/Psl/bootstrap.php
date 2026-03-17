<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\Channel\bounded' => __DIR__ . '/Channel/bounded.php',
        'Psl\Channel\unbounded' => __DIR__ . '/Channel/unbounded.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
