<?php

declare(strict_types=1);

namespace Psl\URL;

use Psl\IRI\Exception\ExceptionInterface;
use Psl\IRI\IRI;
use Psl\URL\Exception\InvalidURLException;

/**
 * Convert an IRI to a URL.
 *
 * Converts to URI first (punycode + percent-encoding), then validates URL constraints.
 *
 * @link https://www.rfc-editor.org/rfc/rfc3987 RFC 3987 - Internationalized Resource Identifiers (IRIs)
 * @link https://www.rfc-editor.org/rfc/rfc3987#section-3.1 RFC 3987 Section 3.1 - Mapping of IRIs to URIs
 *
 * @throws InvalidURLException If the IRI cannot be converted or the resulting URI does not meet URL constraints.
 */
function from_iri(IRI $iri): URL
{
    try {
        $uri = $iri->toURI();
    } catch (ExceptionInterface $e) {
        throw InvalidURLException::forInvalidURI($e->getMessage(), $e);
    }

    return namespace\from_uri($uri);
}
