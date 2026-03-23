<?php

declare(strict_types=1);

namespace Psl\DNS\Record;

use Psl\DateTime\Duration;
use Psl\DNS\EDNS\OptionInterface;

/**
 * An OPT pseudo-record (RFC 6891) used for EDNS0 extension mechanisms.
 *
 * OPT records appear in the additional section and repurpose the class
 * field as UDP payload size and the TTL field as extended RCODE and flags.
 *
 * @api
 */
final class OPTRecord implements RecordInterface
{
    /**
     * {@inheritDoc}
     */
    public RecordType $kind {
        get => RecordType::OPT;
    }

    /**
     * @param string                $name           The domain name (always "" for OPT records).
     * @param Duration              $duration       The TTL duration (not meaningful for OPT; carries extended RCODE in wire format).
     * @param int                   $udpPayloadSize The maximum UDP payload size the sender can handle.
     * @param int                   $extendedRcode  The upper 8 bits of the extended RCODE.
     * @param int                   $version        The EDNS version.
     * @param bool                  $dnssecOk       Whether the DO (DNSSEC OK) bit is set.
     * @param list<OptionInterface> $options        Parsed EDNS0 options.
     */
    public function __construct(
        public readonly string $name,
        public readonly Duration $duration,
        public readonly int $udpPayloadSize,
        public readonly int $extendedRcode,
        public readonly int $version,
        public readonly bool $dnssecOk,
        public readonly array $options,
    ) {}
}
