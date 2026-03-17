<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\Binary\decode_f32' => __DIR__ . '/Binary/decode_f32.php',
        'Psl\Binary\decode_f64' => __DIR__ . '/Binary/decode_f64.php',
        'Psl\Binary\decode_i16' => __DIR__ . '/Binary/decode_i16.php',
        'Psl\Binary\decode_i32' => __DIR__ . '/Binary/decode_i32.php',
        'Psl\Binary\decode_i64' => __DIR__ . '/Binary/decode_i64.php',
        'Psl\Binary\decode_i8' => __DIR__ . '/Binary/decode_i8.php',
        'Psl\Binary\decode_u16' => __DIR__ . '/Binary/decode_u16.php',
        'Psl\Binary\decode_u32' => __DIR__ . '/Binary/decode_u32.php',
        'Psl\Binary\decode_u64' => __DIR__ . '/Binary/decode_u64.php',
        'Psl\Binary\decode_u8' => __DIR__ . '/Binary/decode_u8.php',
        'Psl\Binary\encode_f32' => __DIR__ . '/Binary/encode_f32.php',
        'Psl\Binary\encode_f64' => __DIR__ . '/Binary/encode_f64.php',
        'Psl\Binary\encode_i16' => __DIR__ . '/Binary/encode_i16.php',
        'Psl\Binary\encode_i32' => __DIR__ . '/Binary/encode_i32.php',
        'Psl\Binary\encode_i64' => __DIR__ . '/Binary/encode_i64.php',
        'Psl\Binary\encode_i8' => __DIR__ . '/Binary/encode_i8.php',
        'Psl\Binary\encode_u16' => __DIR__ . '/Binary/encode_u16.php',
        'Psl\Binary\encode_u32' => __DIR__ . '/Binary/encode_u32.php',
        'Psl\Binary\encode_u64' => __DIR__ . '/Binary/encode_u64.php',
        'Psl\Binary\encode_u8' => __DIR__ . '/Binary/encode_u8.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
