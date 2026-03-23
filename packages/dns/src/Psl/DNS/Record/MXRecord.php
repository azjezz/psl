<?php

declare(strict_types=1);

namespace Psl\DNS\Record;

use Psl\DateTime\Duration;

/**
 * An MX record specifying a mail exchange server for a domain (RFC 1035).
 *
 * @api
 */
final class MXRecord implements RecordInterface
{
    /**
     * {@inheritDoc}
     */
    public RecordType $kind {
        get => RecordType::MX;
    }

    /**
     * @param string $name The domain name this record belongs to.
     * @param Duration $duration The time-to-live for this record.
     * @param int $preference The priority of this mail server (lower values are preferred).
     * @param string $exchange The hostname of the mail server.
     */
    public function __construct(
        public readonly string $name,
        public readonly Duration $duration,
        public readonly int $preference,
        public readonly string $exchange,
    ) {}
}
