<?php

declare(strict_types=1);

namespace Psl\DNS\Record;

use Psl\DateTime\Duration;
use Psl\DNS\DNSSEC\Algorithm;

/**
 * An RRSIG record (RFC 4034) containing a signature over an RRset,
 * used to authenticate DNS responses.
 *
 * @api
 *
 * @mago-expect lint:excessive-parameter-list
 */
final class RRSIGRecord implements RecordInterface
{
    /**
     * {@inheritDoc}
     */
    public RecordType $kind {
        get => RecordType::RRSIG;
    }

    /**
     * @param string     $name        The domain name this record belongs to.
     * @param Duration   $duration    The time-to-live for this record.
     * @param RecordType $typeCovered The record type that this signature covers.
     * @param Algorithm  $algorithm   The cryptographic algorithm used to create the signature.
     * @param int        $labels      The number of labels in the original owner name.
     * @param int        $originalTtl The original TTL of the covered RRset.
     * @param int        $expiration  The signature expiration time as a Unix timestamp.
     * @param int        $inception   The signature inception time as a Unix timestamp.
     * @param int        $keyTag      The key tag of the DNSKEY used to create this signature.
     * @param string     $signer      The domain name of the signer.
     * @param string     $signature   The raw binary cryptographic signature.
     */
    public function __construct(
        public readonly string $name,
        public readonly Duration $duration,
        public readonly RecordType $typeCovered,
        public readonly Algorithm $algorithm,
        public readonly int $labels,
        public readonly int $originalTtl,
        public readonly int $expiration,
        public readonly int $inception,
        public readonly int $keyTag,
        public readonly string $signer,
        public readonly string $signature,
    ) {}
}
