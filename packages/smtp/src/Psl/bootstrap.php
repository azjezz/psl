<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\SMTP\Internal\read_greeting' => __DIR__ . '/SMTP/Internal/read_greeting.php',
        'Psl\SMTP\Internal\parse_reply' => __DIR__ . '/SMTP/Internal/parse_reply.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
