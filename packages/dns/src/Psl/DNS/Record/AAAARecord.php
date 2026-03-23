<?php

declare(strict_types=1);

namespace Psl\DNS\Record;

use Psl\DateTime\Duration;
use Psl\IP\Address;

/**
 * An AAAA record mapping a domain name to an IPv6 address (RFC 3596).
 *
 * @api
 */
final class AAAARecord implements RecordInterface
{
    /**
     * {@inheritDoc}
     */
    public RecordType $kind {
        get => RecordType::AAAA;
    }

    /**
     * @param string $name The domain name this record belongs to.
     * @param Duration $duration The time-to-live for this record.
     * @param Address $address The IPv6 address.
     */
    public function __construct(
        public readonly string $name,
        public readonly Duration $duration,
        public readonly Address $address,
    ) {}
}
