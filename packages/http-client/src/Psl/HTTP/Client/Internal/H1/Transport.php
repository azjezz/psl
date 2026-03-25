<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Internal\H1;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\HTTP\Client\Exception;
use Psl\HTTP\Client\Exception\ProtocolException;
use Psl\HTTP\Message;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Transaction;
use Psl\IO;
use Psl\URL\URL;

use function dechex;
use function str_contains;
use function str_starts_with;
use function strlen;
use function strtolower;

/**
 * Stateless HTTP/1.x transport that writes a request and reads the response.
 *
 * Handles the Expect: 100-continue flow (RFC 9110 Section 10.1.1), chunked
 * transfer encoding with trailers (RFC 9112 Section 7.1), and content-length
 * framed bodies. The response is parsed by {@see ResponseReader}.
 *
 * @internal
 *
 * @see H1Connection Owns the stream and delegates here for the wire exchange.
 * @see RequestWriter Serializes the request to the wire.
 * @see ResponseReader Parses the response from the wire.
 */
final class Transport
{
    private function __construct() {}

    /**
     * Send a request over an H1 connection and return the transaction.
     *
     * Handles three request-sending paths:
     * 1. Expect: 100-continue - sends headers first, waits for 100 Continue,
     *    then sends the body (RFC 9110 Section 10.1.1).
     * 2. Normal with body - sends headers + body in one pass.
     * 3. No body - sends headers only.
     *
     * @param H1Connection $connection The connection to exchange on.
     * @param Request $request The request to send.
     * @param URL $url The resolved request URL.
     * @param positive-int $maxHeaderSize Maximum allowed response header size in bytes.
     * @param int $maxResponseBodySize Maximum allowed response body size (0 = unlimited).
     * @param CancellationTokenInterface $cancellation Token to cancel the exchange.
     *
     * @return array{Transaction, bool} The transaction and whether the connection can be reused (keep-alive).
     *
     * @throws Exception\RequestException If the request is structurally invalid.
     * @throws ProtocolException If the response is malformed or exceeds header size limits.
     * @throws IO\Exception\RuntimeException If an I/O error occurs.
     * @throws CancelledException If the cancellation token fires.
     */
    public static function exchange(
        H1Connection $connection,
        Request $request,
        URL $url,
        int $maxHeaderSize,
        int $maxResponseBodySize = 0,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): array {
        $hasExpect = false;
        $expect = $request->headers->get('expect');
        if ($expect !== null && strtolower($expect) === '100-continue') {
            $hasExpect = true;
        }

        try {
            $resolvedTrailers = null;
            $hasTrailers = $request->trailers !== null;

            if ($hasTrailers && $request->body === null) {
                throw Exception\RequestException::forInvalidRequest(
                    'Trailers cannot be sent without a message body (RFC 9110 Section 6.5).',
                );
            }

            if ($hasTrailers) {
                $resolvedTrailers = $request->trailers->await();
                if ($resolvedTrailers->isEmpty()) {
                    $resolvedTrailers = null;
                }
            }

            /** @var null|string $preReadStatusLine */
            $preReadStatusLine = null;

            if ($hasExpect) {
                // Ensure body framing headers are present even though we defer the body.
                // RequestWriter auto-adds transfer-encoding: chunked only when body !== null,
                // but in the Expect path we strip the body before writing headers.
                $expectRequest = $request;
                if ($request->body !== null) {
                    $hasContentLength = $request->headers->has('content-length');
                    $hasTransferEncoding = $request->headers->has('transfer-encoding');
                    if (!$hasContentLength && !$hasTransferEncoding) {
                        $expectRequest = $expectRequest->withHeader('transfer-encoding', 'chunked');
                    }
                }

                RequestWriter::write(
                    $connection->stream,
                    $expectRequest->withBody(null),
                    $url,
                    cancellation: $cancellation,
                );

                $interimLine = $connection->reader->readUntilBounded("\r\n", $maxHeaderSize, $cancellation);
                if ($interimLine !== null && str_starts_with($interimLine, 'HTTP/1.1 100')) {
                    $connection->reader->readUntil("\r\n", $cancellation);

                    if ($request->body !== null) {
                        self::writeBody($connection, $expectRequest, $resolvedTrailers, $cancellation);
                    }
                } elseif ($interimLine !== null) {
                    $preReadStatusLine = $interimLine;
                }
            } else {
                RequestWriter::write($connection->stream, $request, $url, $resolvedTrailers, $cancellation);
            }

            $isHead = $request->method === Message\METHOD_HEAD;
            [$informationalResponses, $response, $keepAlive] = ResponseReader::readWithInformational(
                $connection->reader,
                $maxHeaderSize,
                $cancellation,
                $isHead,
                $preReadStatusLine,
                $maxResponseBodySize,
            );

            $transaction = new Transaction($informationalResponses, null, $response);

            return [$transaction, $keepAlive];
        } catch (IO\Exception\OverflowException) {
            throw ProtocolException::forMalformedResponse('Response exceeds maximum header size.');
        }
    }

    /**
     * Write the request body to the connection, using chunked encoding if applicable.
     *
     * If the request has Transfer-Encoding: chunked, the body is written in
     * chunked format with an optional trailer section (RFC 9112 Section 7.1).
     * Otherwise, the body is written as raw bytes.
     *
     * @param H1Connection $connection The connection to write to.
     * @param Request $request The request containing headers and body.
     * @param null|Message\FieldMap $trailers Resolved trailer fields, or null if none.
     * @param CancellationTokenInterface $cancellation Token to cancel the write.
     *
     * @throws IO\Exception\RuntimeException If writing fails.
     * @throws CancelledException If the write timeout is exceeded.
     */
    private static function writeBody(
        H1Connection $connection,
        Request $request,
        null|Message\FieldMap $trailers,
        CancellationTokenInterface $cancellation,
    ): void {
        $body = $request->body;
        if ($body === null) {
            return;
        }

        $hasChunked = false;
        $te = $request->headers->get('transfer-encoding');
        if ($te !== null && strtolower($te) === 'chunked') {
            $hasChunked = true;
        }

        if ($hasChunked) {
            while (!$body->reachedEndOfDataSource()) {
                $chunk = $body->read(8192, $cancellation);
                if ($chunk === '') {
                    break;
                }

                $connection->stream->writeAll(dechex(strlen($chunk)) . "\r\n" . $chunk . "\r\n", $cancellation);
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
            $connection->stream->writeAll($terminator, $cancellation);
        } else {
            while (!$body->reachedEndOfDataSource()) {
                $chunk = $body->read(8192, $cancellation);
                if ($chunk === '') {
                    break;
                }

                $connection->stream->writeAll($chunk, $cancellation);
            }
        }
    }
}
