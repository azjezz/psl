<?php

declare(strict_types=1);

namespace Psl\DNS\Record;

use Psl\DateTime\Duration;

/**
 * An NSEC3PARAM record (RFC 5155) specifying the NSEC3 parameters used
 * by a zone's authoritative server.
 *
 * @api
 */
final class NSEC3PARAMRecord implements RecordInterface
{
    /**
     * {@inheritDoc}
     */
    public RecordType $kind {
        get => RecordType::NSEC3PARAM;
    }

    /**
     * @param string $name The domain name this record belongs to.
     * @param Duration $duration The time-to-live for this record.
     * @param int $hashAlgorithm The hash algorithm used (1=SHA-1).
     * @param int $flags The NSEC3PARAM flags.
     * @param int $iterations The number of additional hash iterations.
     * @param string $salt The hex-encoded salt value.
     */
    public function __construct(
        public readonly string $name,
        public readonly Duration $duration,
        public readonly int $hashAlgorithm,
        public readonly int $flags,
        public readonly int $iterations,
        public readonly string $salt,
    ) {}
}
