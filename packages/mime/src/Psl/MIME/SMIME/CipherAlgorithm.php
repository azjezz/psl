<?php

declare(strict_types=1);

namespace Psl\MIME\SMIME;

/**
 * Symmetric cipher algorithms for S/MIME content encryption.
 *
 * These algorithms are used by {@see Encryptor} to encrypt the content encryption key (CEK)
 * within a CMS EnvelopedData structure. All options use AES in CBC mode with varying key sizes.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5652 RFC 5652 - Cryptographic Message Syntax (CMS)
 * @link https://datatracker.ietf.org/doc/html/rfc8551 RFC 8551 - S/MIME 4.0 Message Specification
 *
 * @api
 */
enum CipherAlgorithm: string
{
    /**
     * AES with a 128-bit key in Cipher Block Chaining mode.
     */
    case Aes128Cbc = 'aes-128-cbc';

    /**
     * AES with a 192-bit key in Cipher Block Chaining mode.
     */
    case Aes192Cbc = 'aes-192-cbc';

    /**
     * AES with a 256-bit key in Cipher Block Chaining mode.
     */
    case Aes256Cbc = 'aes-256-cbc';
}
