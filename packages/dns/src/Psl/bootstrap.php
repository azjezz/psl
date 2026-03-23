<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\\DNS\\Internal\\tcp_exchange' => __DIR__ . '/DNS/Internal/tcp_exchange.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
