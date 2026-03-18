<?php

declare(strict_types=1);

namespace Psl\Splitter\Http;

use Psl\Str\Byte;

/**
 * Perform an HTTP/1.1 POST request over TLS.
 *
 *  This is *NOT* a general-purpose HTTP client, DO NOT use this for communicating with untrusted servers.
 *
 * @param non-empty-string $url Full HTTPS URL.
 * @param array<non-empty-string, non-empty-string> $headers
 */
function post(string $url, string $body = '', array $headers = []): Response
{
    $headers['content-length'] ??= (string) Byte\length($body);

    return request('POST', $url, $headers, $body);
}
