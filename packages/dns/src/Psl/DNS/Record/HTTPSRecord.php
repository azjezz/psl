<?php

declare(strict_types=1);

namespace Psl\DNS\Record;

use Psl\DateTime\Duration;

/**
 * An HTTPS record for HTTPS service binding (RFC 9460).
 *
 * @api
 */
final class HTTPSRecord implements RecordInterface
{
    /**
     * {@inheritDoc}
     */
    public RecordType $kind {
        get => RecordType::HTTPS;
    }

    /**
     * @param string $name The domain name this record belongs to.
     * @param Duration $duration The time-to-live for this record.
     * @param int $priority The SvcPriority (0 = alias mode, >0 = service mode).
     * @param string $target The TargetName for this service binding.
     * @param array<int, string> $params The SvcParams as key ID to raw value bytes.
     */
    public function __construct(
        public readonly string $name,
        public readonly Duration $duration,
        public readonly int $priority,
        public readonly string $target,
        public readonly array $params,
    ) {}
}
