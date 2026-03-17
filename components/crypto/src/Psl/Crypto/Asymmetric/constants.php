<?php

declare(strict_types=1);

namespace Psl\Crypto\Asymmetric;

use const SODIUM_CRYPTO_BOX_MACBYTES;
use const SODIUM_CRYPTO_BOX_NONCEBYTES;
use const SODIUM_CRYPTO_BOX_PUBLICKEYBYTES;
use const SODIUM_CRYPTO_BOX_SEALBYTES;
use const SODIUM_CRYPTO_BOX_SECRETKEYBYTES;

/**
 * The length of a secret key in bytes.
 *
 * @var positive-int
 */
const SECRET_KEY_BYTES = SODIUM_CRYPTO_BOX_SECRETKEYBYTES;

/**
 * The length of a public key in bytes.
 *
 * @var positive-int
 */
const PUBLIC_KEY_BYTES = SODIUM_CRYPTO_BOX_PUBLICKEYBYTES;

/**
 * The length of the nonce used for authenticated encryption in bytes.
 *
 * @var positive-int
 */
const NONCE_BYTES = SODIUM_CRYPTO_BOX_NONCEBYTES;

/**
 * The overhead added by sealed box encryption in bytes.
 *
 * @var positive-int
 */
const SEAL_BYTES = SODIUM_CRYPTO_BOX_SEALBYTES;

/**
 * The length of the authentication tag in bytes.
 *
 * @var positive-int
 */
const MAC_BYTES = SODIUM_CRYPTO_BOX_MACBYTES;
