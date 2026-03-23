<?php

declare(strict_types=1);

namespace Psl\DNS\Record;

use Psl\DateTime\Duration;

/**
 * An NS record specifying an authoritative nameserver for a domain (RFC 1035).
 *
 * @api
 */
final class NSRecord implements RecordInterface
{
    /**
     * {@inheritDoc}
     */
    public RecordType $kind {
        get => RecordType::NS;
    }

    /**
     * @param string $name The domain name this record belongs to.
     * @param Duration $duration The time-to-live for this record.
     * @param string $host The hostname of the authoritative nameserver.
     */
    public function __construct(
        public readonly string $name,
        public readonly Duration $duration,
        public readonly string $host,
    ) {}
}
