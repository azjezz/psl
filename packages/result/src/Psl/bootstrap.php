<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\Result\collect_stats' => __DIR__ . '/Result/collect_stats.php',
        'Psl\Result\reflect' => __DIR__ . '/Result/reflect.php',
        'Psl\Result\try_catch' => __DIR__ . '/Result/try_catch.php',
        'Psl\Result\wrap' => __DIR__ . '/Result/wrap.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
