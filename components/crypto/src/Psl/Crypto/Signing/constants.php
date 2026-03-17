<?php

declare(strict_types=1);

namespace Psl\Crypto\Signing;

use const SODIUM_CRYPTO_SIGN_BYTES;
use const SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES;
use const SODIUM_CRYPTO_SIGN_SECRETKEYBYTES;

/**
 * The length of a signing secret key in bytes.
 *
 * @var positive-int
 */
const SECRET_KEY_BYTES = SODIUM_CRYPTO_SIGN_SECRETKEYBYTES;

/**
 * The length of a signing public key in bytes.
 *
 * @var positive-int
 */
const PUBLIC_KEY_BYTES = SODIUM_CRYPTO_SIGN_PUBLICKEYBYTES;

/**
 * The length of a detached signature in bytes.
 *
 * @var positive-int
 */
const SIGNATURE_BYTES = SODIUM_CRYPTO_SIGN_BYTES;
