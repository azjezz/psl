<?php

declare(strict_types=1);

(static function (): void {
    $constants = [
        'Psl\\H2\\CONNECTION_PREFACE' => __DIR__ . '/H2/constants.php',
        'Psl\\H2\\FRAME_HEADER_SIZE' => __DIR__ . '/H2/constants.php',
        'Psl\\H2\\DEFAULT_INITIAL_WINDOW_SIZE' => __DIR__ . '/H2/constants.php',
        'Psl\\H2\\DEFAULT_MAX_FRAME_SIZE' => __DIR__ . '/H2/constants.php',
        'Psl\\H2\\MAX_FRAME_SIZE_UPPER_BOUND' => __DIR__ . '/H2/constants.php',
        'Psl\\H2\\DEFAULT_HEADER_TABLE_SIZE' => __DIR__ . '/H2/constants.php',
        'Psl\\H2\\DEFAULT_MAX_CONCURRENT_STREAMS' => __DIR__ . '/H2/constants.php',
        'Psl\\H2\\DEFAULT_MAX_HEADER_LIST_SIZE' => __DIR__ . '/H2/constants.php',
        'Psl\\H2\\MAX_WINDOW_SIZE' => __DIR__ . '/H2/constants.php',
        'Psl\\H2\\MAX_STREAM_ID' => __DIR__ . '/H2/constants.php',
    ];

    $functions = [
        'Psl\\H2\\Frame\\encode' => __DIR__ . '/H2/Frame/encode.php',
        'Psl\\H2\\Frame\\decode' => __DIR__ . '/H2/Frame/decode.php',
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
