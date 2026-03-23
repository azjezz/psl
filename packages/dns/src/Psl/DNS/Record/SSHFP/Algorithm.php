<?php

declare(strict_types=1);

namespace Psl\DNS\Record\SSHFP;

/**
 * SSHFP public key algorithm numbers per IANA registry (RFC 4255, RFC 6594, RFC 7479).
 *
 * @api
 */
enum Algorithm: int
{
    /**
     * RSA public key algorithm.
     */
    case RSA = 1;

    /**
     * DSA public key algorithm.
     */
    case DSA = 2;

    /**
     * ECDSA public key algorithm.
     */
    case ECDSA = 3;

    /**
     * Ed25519 public key algorithm.
     */
    case Ed25519 = 4;
}
