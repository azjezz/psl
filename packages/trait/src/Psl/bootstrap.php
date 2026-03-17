<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\Trait\defined' => __DIR__ . '/Trait/defined.php',
        'Psl\Trait\exists' => __DIR__ . '/Trait/exists.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
