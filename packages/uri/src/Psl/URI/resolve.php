<?php

declare(strict_types=1);

namespace Psl\URI;

use Psl\URI\Internal\Resolver;

/**
 * Resolve a relative reference against a base URI per RFC 3986 Section 5.
 *
 * @throws Exception\InvalidURIException If the resolved URI components are invalid.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3986#section-5
 */
function resolve(URI $base, URI $reference): URI
{
    return Resolver::resolve($base, $reference);
}
