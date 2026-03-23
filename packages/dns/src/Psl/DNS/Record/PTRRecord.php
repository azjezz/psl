<?php

declare(strict_types=1);

namespace Psl\DNS\Record;

use Psl\DateTime\Duration;

/**
 * A PTR record mapping an IP address back to a domain name (RFC 1035).
 *
 * @api
 */
final class PTRRecord implements RecordInterface
{
    /**
     * {@inheritDoc}
     */
    public RecordType $kind {
        get => RecordType::PTR;
    }

    /**
     * @param string $name The reverse-lookup domain name (e.g. "34.216.184.93.in-addr.arpa").
     * @param Duration $duration The time-to-live for this record.
     * @param string $target The domain name this pointer resolves to.
     */
    public function __construct(
        public readonly string $name,
        public readonly Duration $duration,
        public readonly string $target,
    ) {}
}
