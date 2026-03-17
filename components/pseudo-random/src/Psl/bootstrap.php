<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\PseudoRandom\float' => __DIR__ . '/PseudoRandom/float.php',
        'Psl\PseudoRandom\int' => __DIR__ . '/PseudoRandom/int.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
