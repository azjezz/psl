<?php

declare(strict_types=1);

namespace Psl\DNS\Record;

use Psl\DateTime\Duration;
use Psl\DNS\DNSSEC\Algorithm;

/**
 * A DNSKEY record (RFC 4034) containing a public key used to verify
 * RRSIG signatures in the zone.
 *
 * @api
 */
final class DNSKEYRecord implements RecordInterface
{
    /**
     * {@inheritDoc}
     */
    public RecordType $kind {
        get => RecordType::DNSKEY;
    }

    /**
     * @param string    $name      The domain name this record belongs to.
     * @param Duration  $duration  The time-to-live for this record.
     * @param int       $flags     The DNSKEY flags field (256=ZSK, 257=KSK).
     * @param int       $protocol  The protocol field (must be 3 for DNSSEC).
     * @param Algorithm $algorithm The algorithm for this key.
     * @param string    $publicKey The raw binary public key data.
     */
    public function __construct(
        public readonly string $name,
        public readonly Duration $duration,
        public readonly int $flags,
        public readonly int $protocol,
        public readonly Algorithm $algorithm,
        public readonly string $publicKey,
    ) {}
}
