<?php

declare(strict_types=1);

namespace Psl\MIME\DKIM;

/**
 * DKIM canonicalization algorithms for normalizing headers and body before signing.
 *
 * Canonicalization controls how whitespace, header casing, and line endings are treated
 * during signing and verification. The chosen mode is recorded in the "c=" tag of the
 * DKIM-Signature header as "header/body" (e.g., "relaxed/relaxed").
 *
 * @link https://datatracker.ietf.org/doc/html/rfc6376#section-3.4 RFC 6376 - DKIM Signatures, Section 3.4
 *
 * @api
 */
enum Canonicalization: string
{
    /**
     * Preserve headers and body verbatim; only trailing empty lines in the body are removed.
     */
    case Simple = 'simple';

    /**
     * Lowercase header names, unfold headers, and collapse whitespace before signing.
     */
    case Relaxed = 'relaxed';
}
