<?php

declare(strict_types=1);

namespace Psl\DNS\Record;

use Psl\DateTime\Duration;

/**
 * A NAPTR record (RFC 3403) used for URI and service resolution via the
 * Dynamic Delegation Discovery System (DDDS).
 *
 * @api
 */
final class NAPTRRecord implements RecordInterface
{
    /**
     * {@inheritDoc}
     */
    public RecordType $kind {
        get => RecordType::NAPTR;
    }

    /**
     * @param string $name The domain name this record belongs to.
     * @param Duration $duration The time-to-live for this record.
     * @param int $order The processing order for this record (lower values first).
     * @param int $preference The preference within the same order value.
     * @param string $flags Control flags (e.g. "s", "a", "u", "p").
     * @param string $services The service parameters (e.g. "SIP+D2U").
     * @param string $regexp The substitution expression for rewriting the domain.
     * @param string $replacement The next domain name to query (empty if regexp is used).
     */
    public function __construct(
        public readonly string $name,
        public readonly Duration $duration,
        public readonly int $order,
        public readonly int $preference,
        public readonly string $flags,
        public readonly string $services,
        public readonly string $regexp,
        public readonly string $replacement,
    ) {}
}
