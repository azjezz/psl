<?php

declare(strict_types=1);

namespace Psl\Crypto\Symmetric;

use const SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_ABYTES;
use const SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES;
use const SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES;
use const SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES;
use const SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES;

/**
 * The length of a symmetric encryption key in bytes.
 *
 * @var positive-int
 */
const KEY_BYTES = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_KEYBYTES;

/**
 * The length of the nonce used for AEAD encryption in bytes.
 *
 * @var positive-int
 */
const NONCE_BYTES = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES;

/**
 * The length of the authentication tag appended to the ciphertext in bytes.
 *
 * @var positive-int
 */
const TAG_BYTES = SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_ABYTES;

/**
 * The length of the stream encryption header in bytes.
 *
 * @var positive-int
 */
const STREAM_HEADER_BYTES = SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES;

/**
 * The overhead added to each stream chunk in bytes.
 *
 * @var positive-int
 */
const STREAM_TAG_BYTES = SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES;

/**
 * Maximum allowed chunk size for stream encryption in bytes (16 MiB).
 *
 * @var positive-int
 */
const MAX_CHUNK_BYTES = 16 * 1024 * 1024;
