<?php

declare(strict_types=1);

namespace Psl\DNSSEC;

use Psl\DNS\Record\DNSKEYRecord;

/**
 * Holds the result of a DNSSEC trust chain resolution for a zone.
 *
 * @api
 */
final readonly class TrustChainResult
{
    /**
     * @param TrustChainStatus $status The validation outcome for the zone.
     * @param list<DNSKEYRecord> $keys The validated DNSKEY records. Non-empty only when status is Secure.
     * @param null|ChainFailure $failure The reason for failure. Non-null only when status is Bogus.
     */
    public function __construct(
        public TrustChainStatus $status,
        public array $keys,
        public null|ChainFailure $failure = null,
    ) {}
}
