<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\Password\get_information' => __DIR__ . '/Password/get_information.php',
        'Psl\Password\hash' => __DIR__ . '/Password/hash.php',
        'Psl\Password\needs_rehash' => __DIR__ . '/Password/needs_rehash.php',
        'Psl\Password\verify' => __DIR__ . '/Password/verify.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
