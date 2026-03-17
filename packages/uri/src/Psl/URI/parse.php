<?php

declare(strict_types=1);

namespace Psl\URI;

use Psl\URI\Internal\Parser;

/**
 * Parse a URI string per RFC 3986.
 *
 * Applies eager normalization:
 * - Case normalization (scheme and host lowercased)
 * - Percent-encoding normalization (unreserved chars decoded, hex uppercased)
 * - Dot-segment removal (path traversal prevention)
 *
 * Rejects non-ASCII input; use the IRI component for Unicode.
 *
 * @throws Exception\InvalidURIException If the input is not a valid RFC 3986 URI.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3986
 */
function parse(string $input): URI
{
    return Parser::parse($input);
}
