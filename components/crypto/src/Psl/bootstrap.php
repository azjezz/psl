<?php

declare(strict_types=1);

(static function (): void {
    $constants = [
        'Psl\Crypto\Aead\KEY_BYTES' => __DIR__ . '/Crypto/Aead/constants.php',
        'Psl\Crypto\Asymmetric\SECRET_KEY_BYTES' => __DIR__ . '/Crypto/Asymmetric/constants.php',
        'Psl\Crypto\Kdf\KEY_BYTES' => __DIR__ . '/Crypto/Kdf/constants.php',
        'Psl\Crypto\KeyExchange\SECRET_KEY_BYTES' => __DIR__ . '/Crypto/KeyExchange/constants.php',
        'Psl\Crypto\Signing\SECRET_KEY_BYTES' => __DIR__ . '/Crypto/Signing/constants.php',
        'Psl\Crypto\StreamCipher\AES_128_KEY_BYTES' => __DIR__ . '/Crypto/StreamCipher/constants.php',
        'Psl\Crypto\Symmetric\KEY_BYTES' => __DIR__ . '/Crypto/Symmetric/constants.php',
    ];

    $functions = [
        'Psl\Crypto\Aead\decrypt' => __DIR__ . '/Crypto/Aead/decrypt.php',
        'Psl\Crypto\Aead\encrypt' => __DIR__ . '/Crypto/Aead/encrypt.php',
        'Psl\Crypto\Aead\generate_key' => __DIR__ . '/Crypto/Aead/generate_key.php',
        'Psl\Crypto\Asymmetric\decrypt' => __DIR__ . '/Crypto/Asymmetric/decrypt.php',
        'Psl\Crypto\Asymmetric\encrypt' => __DIR__ . '/Crypto/Asymmetric/encrypt.php',
        'Psl\Crypto\Asymmetric\generate_key_pair' => __DIR__ . '/Crypto/Asymmetric/generate_key_pair.php',
        'Psl\Crypto\Asymmetric\open' => __DIR__ . '/Crypto/Asymmetric/open.php',
        'Psl\Crypto\Asymmetric\seal' => __DIR__ . '/Crypto/Asymmetric/seal.php',
        'Psl\Crypto\Hkdf\derive' => __DIR__ . '/Crypto/Hkdf/derive.php',
        'Psl\Crypto\Hkdf\expand' => __DIR__ . '/Crypto/Hkdf/expand.php',
        'Psl\Crypto\Hkdf\extract' => __DIR__ . '/Crypto/Hkdf/extract.php',
        'Psl\Crypto\Internal\call_sodium' => __DIR__ . '/Crypto/Internal/call_sodium.php',
        'Psl\Crypto\Kdf\derive' => __DIR__ . '/Crypto/Kdf/derive.php',
        'Psl\Crypto\Kdf\generate_key' => __DIR__ . '/Crypto/Kdf/generate_key.php',
        'Psl\Crypto\KeyExchange\agree' => __DIR__ . '/Crypto/KeyExchange/agree.php',
        'Psl\Crypto\KeyExchange\generate_key_pair' => __DIR__ . '/Crypto/KeyExchange/generate_key_pair.php',
        'Psl\Crypto\Signing\generate_key_pair' => __DIR__ . '/Crypto/Signing/generate_key_pair.php',
        'Psl\Crypto\Signing\sign' => __DIR__ . '/Crypto/Signing/sign.php',
        'Psl\Crypto\Signing\verify' => __DIR__ . '/Crypto/Signing/verify.php',
        'Psl\Crypto\Symmetric\generate_key' => __DIR__ . '/Crypto/Symmetric/generate_key.php',
        'Psl\Crypto\Symmetric\open' => __DIR__ . '/Crypto/Symmetric/open.php',
        'Psl\Crypto\Symmetric\seal' => __DIR__ . '/Crypto/Symmetric/seal.php',
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
