<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\Html\decode' => __DIR__ . '/Html/decode.php',
        'Psl\Html\decode_special_characters' => __DIR__ . '/Html/decode_special_characters.php',
        'Psl\Html\encode' => __DIR__ . '/Html/encode.php',
        'Psl\Html\encode_special_characters' => __DIR__ . '/Html/encode_special_characters.php',
        'Psl\Html\strip_tags' => __DIR__ . '/Html/strip_tags.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
