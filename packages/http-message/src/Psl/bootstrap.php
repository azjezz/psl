<?php

declare(strict_types=1);

(static function (): void {
    $constants = [
        'Psl\\HTTP\\Message\\STATUS_CONTINUE' => __DIR__ . '/HTTP/Message/constants.php',
        'Psl\\HTTP\\Message\\METHOD_GET' => __DIR__ . '/HTTP/Message/constants.php',
    ];

    $functions = [
        'Psl\\HTTP\\Message\\reason_phrase' => __DIR__ . '/HTTP/Message/reason_phrase.php',
    ];

    foreach ($constants as $constant => $path) {
        if (defined($constant)) {
            continue;
        }

        require_once $path;
    }

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
