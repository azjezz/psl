<?php

declare(strict_types=1);

namespace Psl\DNS\Record;

use Psl\DateTime\Duration;

/**
 * A CNAME record mapping an alias to a canonical domain name (RFC 1035).
 *
 * @api
 */
final class CNAMERecord implements RecordInterface
{
    /**
     * {@inheritDoc}
     */
    public RecordType $kind {
        get => RecordType::CNAME;
    }

    /**
     * @param string $name The alias domain name this record belongs to.
     * @param Duration $duration The time-to-live for this record.
     * @param string $target The canonical domain name this alias points to.
     */
    public function __construct(
        public readonly string $name,
        public readonly Duration $duration,
        public readonly string $target,
    ) {}
}
