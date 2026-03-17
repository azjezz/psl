<?php

declare(strict_types=1);

namespace Psl\IRI;

use Psl\IRI\Internal\HostConverter;
use Psl\IRI\Internal\UnicodeEncoder;
use Psl\URI\Authority\Authority;
use Psl\URI\URI;

/**
 * Convert a URI to an IRI by decoding percent-encoded Unicode and punycode hostnames.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3987#section-3.1
 *
 * @throws \Psl\Punycode\Exception\EncodingException If Punycode decoding fails for an internationalized host.
 */
function from_uri(URI $uri): IRI
{
    $authority = null;
    $uriAuthority = $uri->authority;
    if ($uriAuthority !== null) {
        $host = HostConverter::convertToUnicode($uriAuthority->host);
        $userInfo = $uriAuthority->userInfo !== null ? UnicodeEncoder::decodeFromURI($uriAuthority->userInfo) : null;
        $authority = new Authority($userInfo, $host, $uriAuthority->port);
    }

    $path = UnicodeEncoder::decodeFromURI($uri->path);
    $query = $uri->query !== null ? UnicodeEncoder::decodeFromURI($uri->query) : null;
    $fragment = $uri->fragment !== null ? UnicodeEncoder::decodeFromURI($uri->fragment) : null;

    return new IRI($uri->scheme, $authority, $path, $uri->pathKind, $query, $fragment);
}
