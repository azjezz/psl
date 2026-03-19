<?php

declare(strict_types=1);

namespace Psl\HPACK;

/**
 * Represents a single HPACK header field per RFC 7541.
 *
 * Each header field consists of a lowercase name, a value, and an optional
 * sensitivity flag. Sensitive headers (e.g. authorization, cookie) are encoded
 * with the "never indexed" representation, preventing intermediaries from
 * adding them to their dynamic tables.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc7541#section-6
 */
final readonly class Header
{
    /**
     * Create a new header field.
     *
     * @param non-empty-lowercase-string $name The header field name. Must be lowercase per RFC 7540 Section 8.1.2.
     * @param string $value The header field value. May be empty.
     * @param bool $sensitive Whether the header is sensitive and should never be indexed.
     *                        When true, the encoder uses the "literal header field never indexed"
     *                        representation (RFC 7541 Section 6.2.3).
     */
    public function __construct(
        public string $name,
        public string $value,
        public bool $sensitive = false,
    ) {}
}
