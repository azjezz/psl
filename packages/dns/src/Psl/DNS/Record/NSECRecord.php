<?php

declare(strict_types=1);

namespace Psl\DNS\Record;

use Psl\DateTime\Duration;

/**
 * An NSEC record (RFC 4034) providing authenticated denial of existence
 * by listing the next domain name in the zone and the record types that
 * exist at the current name.
 *
 * @api
 */
final class NSECRecord implements RecordInterface
{
    /**
     * {@inheritDoc}
     */
    public RecordType $kind {
        get => RecordType::NSEC;
    }

    /**
     * @param string $name The domain name this record belongs to.
     * @param Duration $duration The time-to-live for this record.
     * @param string $nextDomainName The next owner name in the canonical ordering of the zone.
     * @param list<RecordType> $types The record types that exist at this owner name.
     */
    public function __construct(
        public readonly string $name,
        public readonly Duration $duration,
        public readonly string $nextDomainName,
        public readonly array $types,
    ) {}
}
