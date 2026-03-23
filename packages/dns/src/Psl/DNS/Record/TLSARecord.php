<?php

declare(strict_types=1);

namespace Psl\DNS\Record;

use Psl\DateTime\Duration;
use Psl\DNS\Record\TLSA\CertificateUsage;
use Psl\DNS\Record\TLSA\MatchingType;
use Psl\DNS\Record\TLSA\Selector;

/**
 * A TLSA record (RFC 6698) used for DNS-Based Authentication of Named
 * Entities (DANE) to associate TLS certificates with domain names.
 *
 * @api
 */
final class TLSARecord implements RecordInterface
{
    /**
     * {@inheritDoc}
     */
    public RecordType $kind {
        get => RecordType::TLSA;
    }

    /**
     * @param string           $name                       The domain name this record belongs to.
     * @param Duration         $duration                   The time-to-live for this record.
     * @param CertificateUsage $certificateUsage           The certificate usage field.
     * @param Selector         $selector                   The selector field.
     * @param MatchingType     $matchingType               The matching type.
     * @param string           $certificateAssociationData The hex-encoded certificate association data.
     */
    public function __construct(
        public readonly string $name,
        public readonly Duration $duration,
        public readonly CertificateUsage $certificateUsage,
        public readonly Selector $selector,
        public readonly MatchingType $matchingType,
        public readonly string $certificateAssociationData,
    ) {}
}
