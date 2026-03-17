<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\Punycode\encode' => __DIR__ . '/Punycode/encode.php',
        'Psl\Punycode\decode' => __DIR__ . '/Punycode/decode.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
