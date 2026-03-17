<?php

declare(strict_types=1);

(static function (): void {
    $constants = [
        'Psl\Encoding\Base64\CHUNK_SIZE' => __DIR__ . '/Encoding/Base64/constants.php',
        'Psl\Encoding\EncodedWord\MAX_ENCODED_WORD_LENGTH' => __DIR__ . '/Encoding/EncodedWord/constants.php',
    ];

    $functions = [
        'Psl\Encoding\Base64\decode' => __DIR__ . '/Encoding/Base64/decode.php',
        'Psl\Encoding\Base64\encode' => __DIR__ . '/Encoding/Base64/encode.php',
        'Psl\Encoding\EncodedWord\Internal\convert_charset' =>
            __DIR__ . '/Encoding/EncodedWord/Internal/convert_charset.php',
        'Psl\Encoding\EncodedWord\Internal\decode_payload' =>
            __DIR__ . '/Encoding/EncodedWord/Internal/decode_payload.php',
        'Psl\Encoding\EncodedWord\Internal\encode_b_words' =>
            __DIR__ . '/Encoding/EncodedWord/Internal/encode_b_words.php',
        'Psl\Encoding\EncodedWord\Internal\encode_q_words' =>
            __DIR__ . '/Encoding/EncodedWord/Internal/encode_q_words.php',
        'Psl\Encoding\EncodedWord\Internal\is_printable_ascii' =>
            __DIR__ . '/Encoding/EncodedWord/Internal/is_printable_ascii.php',
        'Psl\Encoding\EncodedWord\Internal\q_decode' => __DIR__ . '/Encoding/EncodedWord/Internal/q_decode.php',
        'Psl\Encoding\EncodedWord\Internal\q_encode_byte' =>
            __DIR__ . '/Encoding/EncodedWord/Internal/q_encode_byte.php',
        'Psl\Encoding\EncodedWord\Internal\should_use_b_encoding' =>
            __DIR__ . '/Encoding/EncodedWord/Internal/should_use_b_encoding.php',
        'Psl\Encoding\EncodedWord\decode' => __DIR__ . '/Encoding/EncodedWord/decode.php',
        'Psl\Encoding\EncodedWord\encode' => __DIR__ . '/Encoding/EncodedWord/encode.php',
        'Psl\Encoding\Hex\decode' => __DIR__ . '/Encoding/Hex/decode.php',
        'Psl\Encoding\Hex\encode' => __DIR__ . '/Encoding/Hex/encode.php',
        'Psl\Encoding\QuotedPrintable\Internal\encode_octet' =>
            __DIR__ . '/Encoding/QuotedPrintable/Internal/encode_octet.php',
        'Psl\Encoding\QuotedPrintable\decode' => __DIR__ . '/Encoding/QuotedPrintable/decode.php',
        'Psl\Encoding\QuotedPrintable\encode' => __DIR__ . '/Encoding/QuotedPrintable/encode.php',
        'Psl\Encoding\QuotedPrintable\encode_line' => __DIR__ . '/Encoding/QuotedPrintable/encode_line.php',
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
