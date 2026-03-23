<?php

declare(strict_types=1);

(static function (): void {
    /** @var array<string, string> $functions */
    $functions = [];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
