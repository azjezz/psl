<?php

declare(strict_types=1);

namespace Psl\MIME\DKIM;

/**
 * Cryptographic algorithms for DKIM signature generation.
 *
 * Defines the signing algorithm used in the "a=" tag of the DKIM-Signature header.
 * RSA-SHA256 is the widely deployed default; Ed25519-SHA256 offers smaller keys and
 * signatures with comparable security, as specified in RFC 8463.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc6376#section-3.3 RFC 6376 - DKIM Signatures, Section 3.3
 * @link https://datatracker.ietf.org/doc/html/rfc8301 RFC 8301 - Cryptographic Algorithm and Key Usage Update
 * @link https://datatracker.ietf.org/doc/html/rfc8463 RFC 8463 - Ed25519 for DKIM
 *
 * @api
 */
enum Algorithm: string
{
    /**
     * RSA with SHA-256 digest. Keys must be at least 1024 bits per RFC 8301.
     */
    case RsaSha256 = 'rsa-sha256';

    /**
     * Ed25519 with SHA-256 digest, as defined in RFC 8463.
     */
    case Ed25519Sha256 = 'ed25519-sha256';
}
