<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\SecureRandom\bytes' => __DIR__ . '/SecureRandom/bytes.php',
        'Psl\SecureRandom\float' => __DIR__ . '/SecureRandom/float.php',
        'Psl\SecureRandom\int' => __DIR__ . '/SecureRandom/int.php',
        'Psl\SecureRandom\string' => __DIR__ . '/SecureRandom/string.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
