<?php

declare(strict_types=1);

namespace Psl\URL;

use Psl\URI;
use Psl\URL\Exception\InvalidURLException;

/**
 * Parse a URL string.
 *
 * Parses as a URI internally, then validates URL constraints (scheme required,
 * authority required, no rootless paths) and strips default ports.
 *
 * @link https://www.rfc-editor.org/rfc/rfc3986#section-3 RFC 3986 Section 3 - Syntax Components
 * @link https://www.rfc-editor.org/rfc/rfc3986#appendix-B RFC 3986 Appendix B - Parsing a URI Reference
 *
 * @throws InvalidURLException If the input is not a valid URL.
 */
function parse(string $input): URL
{
    try {
        $uri = URI\parse($input);
    } catch (URI\Exception\InvalidURIException $e) {
        throw InvalidURLException::forInvalidURI($e->getMessage(), $e);
    }

    return namespace\from_uri($uri);
}
