<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\URI\parse' => __DIR__ . '/URI/parse.php',
        'Psl\URI\resolve' => __DIR__ . '/URI/resolve.php',
        'Psl\URI\Template\parse' => __DIR__ . '/URI/Template/parse.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
