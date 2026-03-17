<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\Comparison\compare' => __DIR__ . '/Comparison/compare.php',
        'Psl\Comparison\equal' => __DIR__ . '/Comparison/equal.php',
        'Psl\Comparison\greater' => __DIR__ . '/Comparison/greater.php',
        'Psl\Comparison\greater_or_equal' => __DIR__ . '/Comparison/greater_or_equal.php',
        'Psl\Comparison\less' => __DIR__ . '/Comparison/less.php',
        'Psl\Comparison\less_or_equal' => __DIR__ . '/Comparison/less_or_equal.php',
        'Psl\Comparison\not_equal' => __DIR__ . '/Comparison/not_equal.php',
        'Psl\Comparison\sort' => __DIR__ . '/Comparison/sort.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
