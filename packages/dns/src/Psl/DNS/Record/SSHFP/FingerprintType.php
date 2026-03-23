<?php

declare(strict_types=1);

namespace Psl\DNS\Record\SSHFP;

/**
 * SSHFP fingerprint type numbers per IANA registry (RFC 4255, RFC 6594).
 *
 * @api
 */
enum FingerprintType: int
{
    /**
     * SHA-1 fingerprint hash.
     */
    case SHA1 = 1;

    /**
     * SHA-256 fingerprint hash.
     */
    case SHA256 = 2;
}
