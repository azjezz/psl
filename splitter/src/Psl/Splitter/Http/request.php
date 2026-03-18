<?php

declare(strict_types=1);

namespace Psl\Splitter\Http;

use Psl\IO;
use Psl\Math;
use Psl\Str;
use Psl\TLS;
use Psl\URL;

/**
 * Perform an HTTP/1.1 request over TLS.
 *
 * This is a minimal, single-purpose HTTP client. It supports only HTTPS,
 * handles Content-Length and chunked transfer encoding, and follows no redirects.
 *
 * This is *NOT* a general-purpose HTTP client, DO NOT use this for communicating with untrusted servers.
 *
 * @param non-empty-string $method
 * @param non-empty-string $url
 * @param array<non-empty-string, non-empty-string> $headers
 */
function request(string $method, string $url, array $headers = [], string $body = ''): Response
{
    $url = URL\parse($url);

    $stream = TLS\connect($url->authority->host->toString(), $url->authority->port ?? 443);

    $headers['host'] ??= $url->authority->host->toString();
    $headers['connection'] ??= 'close';
    $headers['user-agent'] ??= 'PHP Standard Library Splitter - https://github.com/php-standard-library/php-standard-library';

    $requestTarget = $url->path === '' ? '/' : $url->path;
    if ($url->query !== null) {
        $requestTarget .= '?' . $url->query;
    }

    $raw = $method . ' ' . $requestTarget . " HTTP/1.1\r\n";
    foreach ($headers as $name => $value) {
        $raw .= $name . ': ' . $value . "\r\n";
    }

    $raw .= "\r\n" . $body;

    $stream->writeAll($raw);
    $stream->shutdown();

    $reader = new IO\Reader($stream);

    $statusLine = $reader->readLine() ?? '';
    $status = (int) Str\slice($statusLine, 9, 3);

    $responseHeaders = [];
    while (true) {
        $line = $reader->readLine();
        if ($line === null || $line === '') {
            break;
        }

        $colon = Str\search($line, ':');
        if ($colon === null) {
            continue;
        }

        $name = Str\lowercase(Str\trim(Str\slice($line, 0, $colon)));
        $value = Str\trim(Str\slice($line, $colon + 1));
        if ($name === '' || $value === '') {
            continue;
        }

        $responseHeaders[$name] = $value;
    }

    $responseBody = '';
    if (isset($responseHeaders['content-length'])) {
        $contentLength = Str\to_int($responseHeaders['content-length']);
        if ($contentLength !== null && $contentLength > 0) {
            $responseBody = $reader->readFixedSize($contentLength);
        } else {
            $responseBody = $reader->readAll();
        }
    } elseif (($responseHeaders['transfer-encoding'] ?? '') === 'chunked') {
        while (true) {
            $sizeLine = $reader->readLine();
            if ($sizeLine === null) {
                break;
            }

            $chunkSizeHex = Str\trim(Str\before($sizeLine, ';') ?? $sizeLine);
            if ($chunkSizeHex === '') {
                break;
            }

            $chunkSize = Math\from_base($chunkSizeHex, 16);
            if ($chunkSize <= 0) {
                break;
            }

            $responseBody .= $reader->readFixedSize($chunkSize);

            // consume trailing \r\n after chunk
            $reader->readLine();
        }
    } else {
        $responseBody = $reader->readAll();
    }

    $stream->close();

    /** @var int<100, 599> $status */
    return new Response($status, $responseHeaders, $responseBody);
}
