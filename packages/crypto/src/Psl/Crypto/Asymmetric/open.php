<?php

declare(strict_types=1);

namespace Psl\Crypto\Asymmetric;

use Psl\Crypto\Exception;
use Psl\Crypto\Internal;
use SensitiveParameter;

use function sodium_crypto_box_seal_open;
use function sodium_memzero;

/**
 * Open a sealed message using the recipient's key pair.
 *
 * @throws Exception\DecryptionException If decryption fails.
 */
function open(
    #[SensitiveParameter]
    string $ciphertext,
    #[SensitiveParameter]
    SecretKey $secretKey,
    #[SensitiveParameter]
    PublicKey $publicKey,
): string {
    $keypair = $secretKey->bytes . $publicKey->bytes;

    try {
        $plaintext = Internal\call_sodium(fn() => sodium_crypto_box_seal_open($ciphertext, $keypair));

        if ($plaintext === false) {
            throw new Exception\DecryptionException('Asymmetric decryption failed.');
        }

        return $plaintext;
    } finally {
        sodium_memzero($keypair);
    }
}
