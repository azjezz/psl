<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\IRI\parse' => __DIR__ . '/IRI/parse.php',
        'Psl\IRI\from_uri' => __DIR__ . '/IRI/from_uri.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
