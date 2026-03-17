<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\URL\parse' => __DIR__ . '/URL/parse.php',
        'Psl\URL\from_uri' => __DIR__ . '/URL/from_uri.php',
        'Psl\URL\from_iri' => __DIR__ . '/URL/from_iri.php',
        'Psl\URL\Internal\default_port' => __DIR__ . '/URL/Internal/default_port.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
