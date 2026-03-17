<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\Regex\Internal\call_preg' => __DIR__ . '/Regex/Internal/call_preg.php',
        'Psl\Regex\Internal\get_preg_error' => __DIR__ . '/Regex/Internal/get_preg_error.php',
        'Psl\Regex\capture_groups' => __DIR__ . '/Regex/capture_groups.php',
        'Psl\Regex\every_match' => __DIR__ . '/Regex/every_match.php',
        'Psl\Regex\first_match' => __DIR__ . '/Regex/first_match.php',
        'Psl\Regex\matches' => __DIR__ . '/Regex/matches.php',
        'Psl\Regex\replace' => __DIR__ . '/Regex/replace.php',
        'Psl\Regex\replace_every' => __DIR__ . '/Regex/replace_every.php',
        'Psl\Regex\replace_with' => __DIR__ . '/Regex/replace_with.php',
        'Psl\Regex\split' => __DIR__ . '/Regex/split.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
