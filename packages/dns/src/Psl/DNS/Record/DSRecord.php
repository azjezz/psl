<?php

declare(strict_types=1);

namespace Psl\DNS\Record;

use Psl\DateTime\Duration;
use Psl\DNS\DNSSEC\Algorithm;
use Psl\DNS\DNSSEC\DigestAlgorithm;

/**
 * A DS record (RFC 4034) containing a delegation signer digest used
 * to verify DNSKEY records in the child zone.
 *
 * @api
 */
final class DSRecord implements RecordInterface
{
    /**
     * {@inheritDoc}
     */
    public RecordType $kind {
        get => RecordType::DS;
    }

    /**
     * @param string          $name       The domain name this record belongs to.
     * @param Duration        $duration   The time-to-live for this record.
     * @param int             $keyTag     The key tag of the referenced DNSKEY record.
     * @param Algorithm       $algorithm  The algorithm of the referenced DNSKEY record.
     * @param DigestAlgorithm $digestType The digest algorithm used.
     * @param string          $digest     The hex-encoded digest of the referenced DNSKEY record.
     */
    public function __construct(
        public readonly string $name,
        public readonly Duration $duration,
        public readonly int $keyTag,
        public readonly Algorithm $algorithm,
        public readonly DigestAlgorithm $digestType,
        public readonly string $digest,
    ) {}
}
