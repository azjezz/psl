<?php

declare(strict_types=1);

namespace Psl\DNS\Record;

use Psl\DateTime\Duration;

use function implode;

/**
 * A TXT record containing arbitrary text data associated with a domain (RFC 1035).
 *
 * @api
 */
final class TXTRecord implements RecordInterface
{
    /**
     * {@inheritDoc}
     */
    public RecordType $kind {
        get => RecordType::TXT;
    }

    /**
     * The concatenated text data from all character strings in the record.
     */
    public string $data {
        get => implode('', $this->strings);
    }

    /**
     * @param string $name The domain name this record belongs to.
     * @param Duration $duration The time-to-live for this record.
     * @param non-empty-list<string> $strings The individual character strings from the record.
     */
    public function __construct(
        public readonly string $name,
        public readonly Duration $duration,
        public readonly array $strings,
    ) {}
}
