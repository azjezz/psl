<?php

declare(strict_types=1);

namespace Psl\Crypto\Aead;

use Psl\Crypto\Internal;

use function sodium_crypto_aead_aes256gcm_keygen;
use function sodium_crypto_aead_chacha20poly1305_ietf_keygen;
use function sodium_crypto_aead_xchacha20poly1305_ietf_keygen;

/**
 * Generate a new random AEAD key for the given algorithm.
 */
function generate_key(Algorithm $algorithm): Key
{
    $raw = match ($algorithm) {
        Algorithm::Aes256Gcm => Internal\call_sodium(sodium_crypto_aead_aes256gcm_keygen(...)),
        Algorithm::XChaCha20Poly1305 => Internal\call_sodium(sodium_crypto_aead_xchacha20poly1305_ietf_keygen(...)),
        Algorithm::ChaCha20Poly1305 => Internal\call_sodium(sodium_crypto_aead_chacha20poly1305_ietf_keygen(...)),
    };

    return new Key($raw);
}
