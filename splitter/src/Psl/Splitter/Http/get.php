<?php

declare(strict_types=1);

namespace Psl\Splitter\Http;

/**
 * Perform an HTTP/1.1 GET request over TLS.
 *
 *  This is *NOT* a general-purpose HTTP client, DO NOT use this for communicating with untrusted servers.
 *
 * @param non-empty-string $url Full HTTPS URL.
 * @param array<non-empty-string, non-empty-string> $headers
 */
function get(string $url, array $headers = []): Response
{
    return namespace\request('GET', $url, $headers);
}
