<?php

declare(strict_types=1);

namespace Psl\Crypto\Symmetric;

use Psl\Crypto\Exception;
use SensitiveParameter;

/**
 * Decrypt a ciphertext message with a symmetric key.
 *
 * @throws Exception\DecryptionException If decryption fails.
 */
function open(
    #[SensitiveParameter]
    string $ciphertext,
    #[SensitiveParameter]
    Key $key,
    string $additionalData = '',
): string {
    return new Encryptor($key)->open($ciphertext, $additionalData);
}
