<?php

declare(strict_types=1);

namespace Psl\DNS\Record;

use Psl\DateTime\Duration;

/**
 * A CAA record specifying which certificate authorities may issue certificates for a domain (RFC 8659).
 *
 * @api
 */
final class CAARecord implements RecordInterface
{
    /**
     * {@inheritDoc}
     */
    public RecordType $kind {
        get => RecordType::CAA;
    }

    /**
     * @param string $name The domain name this record belongs to.
     * @param Duration $duration The time-to-live for this record.
     * @param int $flags The CAA flags byte (bit 0 = issuer critical).
     * @param string $tag The property tag (e.g. "issue", "issuewild", "iodef").
     * @param string $value The property value (e.g. the CA's domain name).
     */
    public function __construct(
        public readonly string $name,
        public readonly Duration $duration,
        public readonly int $flags,
        public readonly string $tag,
        public readonly string $value,
    ) {}
}
