<?php

declare(strict_types=1);

namespace Psl\MIME\SMIME;

/**
 * Message digest (hash) algorithms for S/MIME signing and verification.
 *
 * Used by {@see Signer} when computing the message digest included in the SignedData structure,
 * and by {@see Verifier} when validating signatures. SHA-1 is included for backward compatibility
 * but SHA-256 or stronger is recommended per RFC 8551.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5652 RFC 5652 - Cryptographic Message Syntax (CMS)
 * @link https://datatracker.ietf.org/doc/html/rfc8551 RFC 8551 - S/MIME 4.0 Message Specification
 *
 * @api
 */
enum DigestAlgorithm: string
{
    /**
     * SHA-1 (160-bit). Deprecated for new signatures; retained for verification of legacy messages.
     */
    case Sha1 = 'sha1';

    /**
     * SHA-256 (256-bit). Recommended default per RFC 8551.
     */
    case Sha256 = 'sha256';

    /**
     * SHA-384 (384-bit).
     */
    case Sha384 = 'sha384';

    /**
     * SHA-512 (512-bit).
     */
    case Sha512 = 'sha512';
}
