<?php

declare(strict_types=1);

namespace Psl\DNS\EDNS;

/**
 * Known Extended DNS Error info codes (RFC 8914).
 *
 * @api
 */
enum ExtendedDNSError: int
{
    /**
     * An unspecified extended error.
     */
    case Other = 0;

    /**
     * The DNSKEY algorithm is not supported by the validator.
     */
    case UnsupportedDnskeyAlgorithm = 1;

    /**
     * The DS digest type is not supported by the validator.
     */
    case UnsupportedDsDigestType = 2;

    /**
     * The answer was served from stale cache data.
     */
    case StaleAnswer = 3;

    /**
     * The answer was forged or manipulated.
     */
    case ForgedAnswer = 4;

    /**
     * DNSSEC validation state is indeterminate.
     */
    case DnssecIndeterminate = 5;

    /**
     * DNSSEC validation determined the response is bogus (invalid).
     */
    case DnssecBogus = 6;

    /**
     * The DNSSEC signature has expired.
     */
    case SignatureExpired = 7;

    /**
     * The DNSSEC signature inception time is in the future.
     */
    case SignatureNotYetValid = 8;

    /**
     * The required DNSKEY record is missing.
     */
    case DnskeyMissing = 9;

    /**
     * The required RRSIG records are missing.
     */
    case RrsigsMissing = 10;

    /**
     * No zone key bit was set in the DNSKEY record.
     */
    case NoZoneKeyBitSet = 11;

    /**
     * The required NSEC or NSEC3 record is missing.
     */
    case NsecMissing = 12;

    /**
     * The response was synthesized from a cached error.
     */
    case CachedError = 13;

    /**
     * The resolver is not ready to serve the query.
     */
    case NotReady = 14;

    /**
     * The query was blocked by administrative policy.
     */
    case Blocked = 15;

    /**
     * The query was censored by administrative policy.
     */
    case Censored = 16;

    /**
     * The query was filtered by administrative policy.
     */
    case Filtered = 17;

    /**
     * The query is prohibited by policy.
     */
    case Prohibited = 18;

    /**
     * A stale NXDOMAIN answer was served from cache.
     */
    case StaleNxdomainAnswer = 19;

    /**
     * The server is not authoritative for the requested zone.
     */
    case NotAuthoritative = 20;

    /**
     * The requested operation is not supported.
     */
    case NotSupported = 21;

    /**
     * No authoritative nameserver could be reached.
     */
    case NoReachableAuthority = 22;

    /**
     * A network error occurred while contacting an authority.
     */
    case NetworkError = 23;

    /**
     * The zone data is invalid or corrupt.
     */
    case InvalidData = 24;
}
