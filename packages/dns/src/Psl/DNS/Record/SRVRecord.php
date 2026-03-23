<?php

declare(strict_types=1);

namespace Psl\DNS\Record;

use Psl\DateTime\Duration;

/**
 * An SRV record specifying the location of a service (host and port) per RFC 2782.
 *
 * @api
 */
final class SRVRecord implements RecordInterface
{
    /**
     * {@inheritDoc}
     */
    public RecordType $kind {
        get => RecordType::SRV;
    }

    /**
     * @param string $name The domain name this record belongs to.
     * @param Duration $duration The time-to-live for this record.
     * @param int $priority The priority of this target host (lower values are preferred).
     * @param int $weight A relative weight for records with the same priority.
     * @param int $port The TCP or UDP port on which the service is available.
     * @param string $target The hostname of the machine providing the service.
     */
    public function __construct(
        public readonly string $name,
        public readonly Duration $duration,
        public readonly int $priority,
        public readonly int $weight,
        public readonly int $port,
        public readonly string $target,
    ) {}
}
