<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\Runtime\get_extensions' => __DIR__ . '/Runtime/get_extensions.php',
        'Psl\Runtime\get_sapi' => __DIR__ . '/Runtime/get_sapi.php',
        'Psl\Runtime\get_version' => __DIR__ . '/Runtime/get_version.php',
        'Psl\Runtime\get_version_details' => __DIR__ . '/Runtime/get_version_details.php',
        'Psl\Runtime\get_version_id' => __DIR__ . '/Runtime/get_version_id.php',
        'Psl\Runtime\get_zend_extensions' => __DIR__ . '/Runtime/get_zend_extensions.php',
        'Psl\Runtime\get_zend_version' => __DIR__ . '/Runtime/get_zend_version.php',
        'Psl\Runtime\has_extension' => __DIR__ . '/Runtime/has_extension.php',
        'Psl\Runtime\is_debug' => __DIR__ . '/Runtime/is_debug.php',
        'Psl\Runtime\is_thread_safe' => __DIR__ . '/Runtime/is_thread_safe.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
