<?php

declare(strict_types=1);

namespace Psl\Crypto\KeyExchange;

use const SODIUM_CRYPTO_SCALARMULT_BYTES;
use const SODIUM_CRYPTO_SCALARMULT_SCALARBYTES;

/**
 * The length of a key exchange secret key in bytes.
 *
 * @var positive-int
 */
const SECRET_KEY_BYTES = SODIUM_CRYPTO_SCALARMULT_SCALARBYTES;

/**
 * The length of a key exchange public key in bytes.
 *
 * @var positive-int
 */
const PUBLIC_KEY_BYTES = SODIUM_CRYPTO_SCALARMULT_BYTES;

/**
 * The length of the shared secret in bytes.
 *
 * @var positive-int
 */
const SHARED_SECRET_BYTES = SODIUM_CRYPTO_SCALARMULT_BYTES;
