<?php

declare(strict_types=1);

namespace Psl\IRI;

use Psl\IRI\Internal\IRIParser;

/**
 * Parse an IRI string per RFC 3987.
 *
 * Accepts Unicode input. Applies NFC normalization, validates IRI-specific
 * character ranges, and normalizes the result.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3987#section-2.2
 *
 * @throws Exception\InvalidIRIException If the input contains invalid Unicode characters for an IRI.
 */
function parse(string $input): IRI
{
    return IRIParser::parse($input);
}
