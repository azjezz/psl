<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\Class\defined' => __DIR__ . '/Class/defined.php',
        'Psl\Class\exists' => __DIR__ . '/Class/exists.php',
        'Psl\Class\has_constant' => __DIR__ . '/Class/has_constant.php',
        'Psl\Class\has_method' => __DIR__ . '/Class/has_method.php',
        'Psl\Class\is_abstract' => __DIR__ . '/Class/is_abstract.php',
        'Psl\Class\is_final' => __DIR__ . '/Class/is_final.php',
        'Psl\Class\is_readonly' => __DIR__ . '/Class/is_readonly.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
