<?php

declare(strict_types=1);

namespace Psl\Crypto\Aead;

use const SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES;

/**
 * The length of an AEAD encryption key in bytes.
 *
 * All supported AEAD algorithms use a 32-byte key.
 *
 * @var positive-int
 */
const KEY_BYTES = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES;
