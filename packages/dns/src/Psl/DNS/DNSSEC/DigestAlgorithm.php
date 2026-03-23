<?php

declare(strict_types=1);

namespace Psl\DNS\DNSSEC;

/**
 * DS record digest algorithm numbers per IANA registry (RFC 4034, RFC 4509, RFC 6605).
 *
 * @api
 */
enum DigestAlgorithm: int
{
    /**
     * SHA-1 digest (RFC 4034).
     */
    case SHA1 = 1;

    /**
     * SHA-256 digest (RFC 4509).
     */
    case SHA256 = 2;

    /**
     * SHA-384 digest (RFC 6605).
     */
    case SHA384 = 4;
}
