<?php

declare(strict_types=1);

namespace Psl\URL;

use Psl\URI\Authority\Authority;
use Psl\URI\URI;
use Psl\URL\Exception\InvalidURLException;

/**
 * Convert a URI to a URL.
 *
 * Validates URL constraints (scheme required, authority required, no rootless paths)
 * and strips default ports for known schemes.
 *
 * @link https://www.rfc-editor.org/rfc/rfc3986#section-3 RFC 3986 Section 3 - Syntax Components
 *
 * @throws InvalidURLException If the URI does not meet URL constraints.
 */
function from_uri(URI $uri): URL
{
    if ($uri->scheme === null) {
        throw InvalidURLException::forMissingScheme();
    }

    if ($uri->authority === null) {
        throw InvalidURLException::forMissingAuthority();
    }

    if ($uri->path !== '' && $uri->path[0] !== '/') {
        throw InvalidURLException::forRootlessPath();
    }

    $port = $uri->authority->port;
    if ($port !== null && Internal\default_port($uri->scheme) === $port) {
        $port = null;
    }

    $authority = $port !== $uri->authority->port
        ? new Authority($uri->authority->userInfo, $uri->authority->host, $port)
        : $uri->authority;

    return new URL($uri->scheme, $authority, $uri->path, $uri->query, $uri->fragment);
}
