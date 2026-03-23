<?php

declare(strict_types=1);

namespace Psl\DNSSEC;

/**
 * Represents the outcome of a DNSSEC trust chain validation.
 *
 * @api
 */
enum TrustChainStatus
{
    /**
     * The chain of trust was fully validated from the root trust anchor
     * to the target zone, and the DNSKEY records are cryptographically verified.
     */
    case Secure;

    /**
     * The target zone is provably unsigned. A DS non-existence proof (via NSEC or NSEC3)
     * confirmed that no delegation signer exists, so the zone is legitimately not secured
     * by DNSSEC.
     */
    case Insecure;

    /**
     * The chain of trust validation failed. The specific reason is available
     * via {@see TrustChainResult::$failure}.
     */
    case Bogus;
}
