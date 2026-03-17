<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\Json\decode' => __DIR__ . '/Json/decode.php',
        'Psl\Json\encode' => __DIR__ . '/Json/encode.php',
        'Psl\Json\typed' => __DIR__ . '/Json/typed.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
