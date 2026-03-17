<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\Terminal\Layout\Internal\resolve_constraint' =>
            __DIR__ . '/Terminal/Layout/Internal/resolve_constraint.php',
        'Psl\Terminal\Layout\Internal\resolve_max' => __DIR__ . '/Terminal/Layout/Internal/resolve_max.php',
        'Psl\Terminal\Layout\Internal\resolve_min' => __DIR__ . '/Terminal/Layout/Internal/resolve_min.php',
        'Psl\Terminal\Layout\Internal\solve' => __DIR__ . '/Terminal/Layout/Internal/solve.php',
        'Psl\Terminal\Layout\fill' => __DIR__ . '/Terminal/Layout/fill.php',
        'Psl\Terminal\Layout\fixed' => __DIR__ . '/Terminal/Layout/fixed.php',
        'Psl\Terminal\Layout\horizontal' => __DIR__ . '/Terminal/Layout/horizontal.php',
        'Psl\Terminal\Layout\max' => __DIR__ . '/Terminal/Layout/max.php',
        'Psl\Terminal\Layout\min' => __DIR__ . '/Terminal/Layout/min.php',
        'Psl\Terminal\Layout\vertical' => __DIR__ . '/Terminal/Layout/vertical.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
