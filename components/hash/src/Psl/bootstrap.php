<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\Hash\Hmac\hash' => __DIR__ . '/Hash/Hmac/hash.php',
        'Psl\Hash\equals' => __DIR__ . '/Hash/equals.php',
        'Psl\Hash\hash' => __DIR__ . '/Hash/hash.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
