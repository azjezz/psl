<?php

declare(strict_types=1);

namespace Psl\Crypto\Kdf;

use const SODIUM_CRYPTO_KDF_BYTES_MAX;
use const SODIUM_CRYPTO_KDF_BYTES_MIN;
use const SODIUM_CRYPTO_KDF_CONTEXTBYTES;
use const SODIUM_CRYPTO_KDF_KEYBYTES;

/**
 * The length of a KDF master key in bytes.
 *
 * @var positive-int
 */
const KEY_BYTES = SODIUM_CRYPTO_KDF_KEYBYTES;

/**
 * The required length of the KDF context string in bytes.
 *
 * @var positive-int
 */
const CONTEXT_BYTES = SODIUM_CRYPTO_KDF_CONTEXTBYTES;

/**
 * The minimum derived key length in bytes.
 *
 * @var positive-int
 */
const DERIVED_MIN_BYTES = SODIUM_CRYPTO_KDF_BYTES_MIN;

/**
 * The maximum derived key length in bytes.
 *
 * @var positive-int
 */
const DERIVED_MAX_BYTES = SODIUM_CRYPTO_KDF_BYTES_MAX;
