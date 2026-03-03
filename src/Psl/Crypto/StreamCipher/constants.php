<?php

declare(strict_types=1);

namespace Psl\Crypto\StreamCipher;

/**
 * Key length for AES-128-CTR in bytes.
 *
 * @var positive-int
 */
const AES_128_KEY_BYTES = 16;

/**
 * Key length for AES-256-CTR and XChaCha20 in bytes.
 *
 * @var positive-int
 */
const AES_256_KEY_BYTES = 32;

/**
 * Key length for XChaCha20 in bytes.
 *
 * @var positive-int
 */
const XCHACHA20_KEY_BYTES = 32;

/**
 * IV length for AES-CTR (128 and 256) in bytes.
 *
 * @var positive-int
 */
const AES_CTR_IV_BYTES = 16;

/**
 * IV (nonce) length for XChaCha20 in bytes.
 *
 * @var positive-int
 */
const XCHACHA20_IV_BYTES = 24;

/**
 * Block size for AES-CTR in bytes.
 *
 * @var positive-int
 */
const AES_CTR_BLOCK_BYTES = 16;

/**
 * Block size for XChaCha20 in bytes.
 *
 * @var positive-int
 */
const XCHACHA20_BLOCK_BYTES = 64;
