<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\Interface\defined' => __DIR__ . '/Interface/defined.php',
        'Psl\Interface\exists' => __DIR__ . '/Interface/exists.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
