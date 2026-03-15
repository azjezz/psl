<?php

declare(strict_types=1);

namespace Psl\Crypto\Asymmetric;

use Psl\Crypto\Exception;
use Psl\Crypto\Internal;
use Psl\Str\Byte;
use SensitiveParameter;

use function sodium_crypto_box_open;
use function sodium_memzero;

/**
 * Decrypt a message using authenticated public-key decryption.
 *
 * @throws Exception\DecryptionException If decryption fails.
 */
function decrypt(
    #[SensitiveParameter]
    string $ciphertext,
    #[SensitiveParameter]
    SecretKey $recipientSecretKey,
    #[SensitiveParameter]
    PublicKey $senderPublicKey,
): string {
    if (Byte\length($ciphertext) < namespace\NONCE_BYTES) {
        throw new Exception\DecryptionException('Ciphertext is too short.');
    }

    $nonce = Byte\slice($ciphertext, 0, namespace\NONCE_BYTES);
    $encrypted = Byte\slice($ciphertext, namespace\NONCE_BYTES);
    $keypair = $recipientSecretKey->bytes . $senderPublicKey->bytes;

    try {
        $plaintext = Internal\call_sodium(fn() => sodium_crypto_box_open($encrypted, $nonce, $keypair));

        if ($plaintext === false) {
            throw new Exception\DecryptionException('Authenticated asymmetric decryption failed.');
        }

        return $plaintext;
    } finally {
        sodium_memzero($keypair);
    }
}
