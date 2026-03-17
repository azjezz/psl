<?php

declare(strict_types=1);

(static function (): void {
    $functions = [
        'Psl\Socks\Internal\reply_message' => __DIR__ . '/Socks/Internal/reply_message.php',
        'Psl\Socks\Internal\socks5_authenticate' => __DIR__ . '/Socks/Internal/socks5_authenticate.php',
        'Psl\Socks\Internal\socks5_handshake' => __DIR__ . '/Socks/Internal/socks5_handshake.php',
    ];

    foreach ($functions as $function => $path) {
        if (function_exists($function)) {
            continue;
        }

        require_once $path;
    }
})();
