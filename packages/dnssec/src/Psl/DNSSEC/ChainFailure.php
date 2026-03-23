<?php

declare(strict_types=1);

namespace Psl\DNSSEC;

/**
 * Describes the specific reason a DNSSEC trust chain validation resulted in a Bogus status.
 *
 * @api
 */
enum ChainFailure
{
    /**
     * An RRSIG signature could not be verified against any available DNSKEY.
     */
    case SignatureVerificationFailed;

    /**
     * No DNSKEY records were found for the zone being validated.
     */
    case MissingDnskey;

    /**
     * No DS records were found for the zone, and no valid NSEC/NSEC3
     * proof of non-existence was provided.
     */
    case MissingDs;

    /**
     * The DNSKEY RRset for a zone was not signed (no RRSIG covering DNSKEY).
     */
    case UnsignedDnskey;

    /**
     * The DS RRset for a zone was not signed (no RRSIG covering DS).
     */
    case UnsignedDs;

    /**
     * The root DNSKEY records did not match the configured trust anchor DS records.
     */
    case TrustAnchorMismatch;

    /**
     * The number of DNSKEY or RRSIG records exceeded the safety limit,
     * preventing potential denial-of-service via excessive cryptographic work.
     */
    case ResourceExhaustion;

    /**
     * The chain of trust is broken. A required parent zone's validated keys
     * were not available, or no DS-to-DNSKEY link could be established.
     */
    case ChainBroken;
}
