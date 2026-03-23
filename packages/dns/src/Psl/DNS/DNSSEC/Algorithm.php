<?php

declare(strict_types=1);

namespace Psl\DNS\DNSSEC;

/**
 * DNSSEC algorithm numbers per IANA registry (RFC 4034, RFC 5702, RFC 6605, RFC 8080).
 *
 * @api
 */
enum Algorithm: int
{
    /**
     * RSA with SHA-1 signature algorithm.
     */
    case RSASHA1 = 5;

    /**
     * RSA with SHA-1 using NSEC3 (RFC 5155).
     */
    case RSASHA1_NSEC3_SHA1 = 7;

    /**
     * RSA with SHA-256 signature algorithm (RFC 5702).
     */
    case RSASHA256 = 8;

    /**
     * RSA with SHA-512 signature algorithm (RFC 5702).
     */
    case RSASHA512 = 10;

    /**
     * ECDSA Curve P-256 with SHA-256 (RFC 6605).
     */
    case ECDSAP256SHA256 = 13;

    /**
     * ECDSA Curve P-384 with SHA-384 (RFC 6605).
     */
    case ECDSAP384SHA384 = 14;

    /**
     * Ed25519 Edwards-curve signature algorithm (RFC 8080).
     */
    case ED25519 = 15;

    /**
     * Ed448 Edwards-curve signature algorithm (RFC 8080).
     */
    case ED448 = 16;
}
