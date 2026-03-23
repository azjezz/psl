<?php

declare(strict_types=1);

namespace Psl\DNS\Record\TLSA;

/**
 * TLSA matching type field values per RFC 6698.
 *
 * @api
 */
enum MatchingType: int
{
    /**
     * Exact match against the full selected content.
     */
    case Exact = 0;

    /**
     * Match against a SHA-256 hash of the selected content.
     */
    case SHA256 = 1;

    /**
     * Match against a SHA-512 hash of the selected content.
     */
    case SHA512 = 2;
}
