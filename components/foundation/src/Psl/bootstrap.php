<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\invariant' => __DIR__ . '/invariant.php',
        'Psl\invariant_violation' => __DIR__ . '/invariant_violation.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
