<?php

declare(strict_types=1);

namespace Psl\DNS\Record;

use Psl\DateTime\Duration;

/**
 * An NSEC3 record (RFC 5155) providing hashed authenticated denial of
 * existence, preventing zone enumeration.
 *
 * @api
 */
final class NSEC3Record implements RecordInterface
{
    /**
     * {@inheritDoc}
     */
    public RecordType $kind {
        get => RecordType::NSEC3;
    }

    /**
     * @param string $name The domain name this record belongs to.
     * @param Duration $duration The time-to-live for this record.
     * @param int $hashAlgorithm The hash algorithm used (1=SHA-1).
     * @param int $flags The NSEC3 flags (1=Opt-Out).
     * @param int $iterations The number of additional hash iterations.
     * @param string $salt The hex-encoded salt value.
     * @param string $nextHashedOwnerName The base32hex-encoded hash of the next owner name.
     * @param list<RecordType> $types The record types that exist at the hashed owner name.
     */
    public function __construct(
        public readonly string $name,
        public readonly Duration $duration,
        public readonly int $hashAlgorithm,
        public readonly int $flags,
        public readonly int $iterations,
        public readonly string $salt,
        public readonly string $nextHashedOwnerName,
        public readonly array $types,
    ) {}
}
