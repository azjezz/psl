<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Internal\H1;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\HTTP\Message\FieldMap;
use Psl\HTTP\Message\ProtocolVersion;
use Psl\HTTP\Message\Request;
use Psl\IO;
use Psl\Network;
use Psl\URL\URL;

use function dechex;
use function str_contains;
use function strlen;
use function strtolower;

/**
 * Serializes and writes an HTTP/1.x request message to a network stream.
 *
 * Produces the request-line, headers, and body per RFC 9112 Section 2.1.
 * When no Content-Length or Transfer-Encoding header is present and the request
 * has a body, chunked transfer encoding is applied automatically
 * (RFC 9112 Section 7.1).
 *
 * Header fields containing CR or LF characters are silently skipped to prevent
 * header injection attacks.
 *
 * @internal
 *
 * @see Transport Uses this writer to send requests during an exchange.
 */
final class RequestWriter
{
    private function __construct() {}

    /**
     * Write a complete HTTP/1.x request (request-line, headers, body) to the stream.
     *
     * Body framing is determined by the presence of Content-Length or
     * Transfer-Encoding headers. If neither is present and a body exists,
     * chunked transfer encoding is used with an optional trailer section.
     *
     * @param Network\StreamInterface $stream The stream to write to.
     * @param Request $request The request to serialize.
     * @param URL $url The resolved URL (used for the Host header if not already present).
     * @param null|FieldMap $trailers Trailer fields to append after a chunked body, or null.
     * @param CancellationTokenInterface $cancellation Token to cancel the write.
     *
     * @throws IO\Exception\RuntimeException If writing to the stream fails or the request target contains invalid characters.
     * @throws CancelledException If the write timeout is exceeded.
     */
    public static function write(
        Network\StreamInterface $stream,
        Request $request,
        URL $url,
        null|FieldMap $trailers = null,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): void {
        $target = $request->requestTarget;
        if (str_contains($target, "\r") || str_contains($target, "\n") || str_contains($target, "\0")) {
            throw new IO\Exception\RuntimeException('Request target contains invalid characters');
        }

        $protocol = $request->protocolVersion === ProtocolVersion::V10 ? '1.0' : '1.1';
        $head = $request->method . ' ' . $target . ' HTTP/' . $protocol . "\r\n";

        if (!$request->headers->has('host')) {
            $host = $url->authority->host->toString();
            if ($url->authority->port !== null) {
                $host .= ':' . $url->authority->port;
            }

            $head .= 'host: ' . $host . "\r\n";
        }

        $hasContentLength = false;
        $hasTransferEncoding = false;

        foreach ($request->headers as [$name, $value]) {
            if (
                str_contains($name, "\r")
                || str_contains($name, "\n")
                || str_contains($value, "\r")
                || str_contains($value, "\n")
            ) {
                continue;
            }

            $head .= $name . ': ' . $value . "\r\n";

            $nameLen = strlen($name);
            if (!$hasContentLength && $nameLen === 14 && strtolower($name) === 'content-length') {
                $hasContentLength = true;
            } elseif (!$hasTransferEncoding && $nameLen === 17 && strtolower($name) === 'transfer-encoding') {
                $hasTransferEncoding = true;
            }
        }

        $body = $request->body;
        if ($body !== null && !$hasContentLength && !$hasTransferEncoding) {
            $head .= "transfer-encoding: chunked\r\n";
        }

        $head .= "\r\n";

        $stream->writeAll($head, $cancellation);

        if ($body === null) {
            return;
        }

        if ($hasContentLength || $hasTransferEncoding) {
            self::writeRawBody($stream, $body, $cancellation);
        } else {
            self::writeChunkedBody($stream, $body, $trailers, $cancellation);
        }
    }

    /**
     * Write the request body using chunked transfer encoding (RFC 9112 Section 7.1).
     *
     * Each chunk is prefixed with its size in hexadecimal. The body is terminated
     * with a zero-length chunk, optionally followed by trailer fields.
     *
     * @param Network\StreamInterface $stream The stream to write to.
     * @param IO\ReadHandleInterface $body The request body to stream.
     * @param null|FieldMap $trailers Trailer fields, or null.
     * @param CancellationTokenInterface $cancellation Token to cancel the write.
     *
     * @throws IO\Exception\RuntimeException If writing fails.
     * @throws CancelledException If the write timeout is exceeded.
     */
    private static function writeChunkedBody(
        Network\StreamInterface $stream,
        IO\ReadHandleInterface $body,
        null|FieldMap $trailers,
        CancellationTokenInterface $cancellation,
    ): void {
        while (!$body->reachedEndOfDataSource()) {
            $chunk = $body->read(8192, $cancellation);
            if ($chunk === '') {
                break;
            }

            $stream->writeAll(dechex(strlen($chunk)) . "\r\n" . $chunk . "\r\n", $cancellation);
        }

        $terminator = "0\r\n";
        if ($trailers !== null && !$trailers->isEmpty()) {
            foreach ($trailers as [$name, $value]) {
                if (
                    str_contains($name, "\r")
                    || str_contains($name, "\n")
                    || str_contains($value, "\r")
                    || str_contains($value, "\n")
                ) {
                    continue;
                }

                $terminator .= $name . ': ' . $value . "\r\n";
            }
        }

        $terminator .= "\r\n";
        $stream->writeAll($terminator, $cancellation);
    }

    /**
     * Write the request body as raw bytes without any transfer encoding.
     *
     * Used when Content-Length or an explicit Transfer-Encoding header is
     * already present on the request.
     *
     * @param Network\StreamInterface $stream The stream to write to.
     * @param IO\ReadHandleInterface $body The request body to stream.
     * @param CancellationTokenInterface $cancellation Token to cancel the write.
     *
     * @throws IO\Exception\RuntimeException If writing fails.
     * @throws CancelledException If the write timeout is exceeded.
     */
    private static function writeRawBody(
        Network\StreamInterface $stream,
        IO\ReadHandleInterface $body,
        CancellationTokenInterface $cancellation,
    ): void {
        while (!$body->reachedEndOfDataSource()) {
            $chunk = $body->read(8192, $cancellation);
            if ($chunk === '') {
                break;
            }

            $stream->writeAll($chunk, $cancellation);
        }
    }
}
