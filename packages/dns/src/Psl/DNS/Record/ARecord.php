<?php

declare(strict_types=1);

namespace Psl\DNS\Record;

use Psl\DateTime\Duration;
use Psl\IP\Address;

/**
 * An A record mapping a domain name to an IPv4 address (RFC 1035).
 *
 * @api
 */
final class ARecord implements RecordInterface
{
    /**
     * {@inheritDoc}
     */
    public RecordType $kind {
        get => RecordType::A;
    }

    /**
     * @param string $name The domain name this record belongs to.
     * @param Duration $duration The time-to-live for this record.
     * @param Address $address The IPv4 address.
     */
    public function __construct(
        public readonly string $name,
        public readonly Duration $duration,
        public readonly Address $address,
    ) {}
}
