<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Internal\H2;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\H2\ClientConnectionInterface;
use Psl\H2\Exception\ExceptionInterface;
use Psl\HPACK\Header;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Exception\RequestException;
use Psl\HTTP\Client\Exception\RuntimeException;
use Psl\HTTP\Message\ProtocolVersion;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Response;
use Psl\HTTP\Message\Transaction;
use Psl\IO;
use Psl\URL;

use function strtolower;

/**
 * Manages one HTTP/2 request/response exchange on a single stream.
 *
 * Creates an {@see H2Stream}, registers it with the multiplexer, sends request
 * frames, and waits for the response via the stream's deferred. The response
 * body is read lazily through {@see ResponseBodyHandle} which reads from the
 * H2Stream's buffer - fully decoupled from the multiplexer's read fiber.
 *
 * @internal
 */
final class StreamExchange
{
    private function __construct() {}

    /**
     * @throws RequestException If the request is structurally invalid.
     * @throws RuntimeException If the H2 connection is closed or a protocol error occurs.
     * @throws CancelledException If the cancellation token fires.
     * @throws ExceptionInterface If an H2 framing error occurs.
     * @throws IO\Exception\RuntimeException If an I/O error occurs.
     */
    public static function exchange(
        H2Session $session,
        Request $request,
        URL\URL $url,
        ClientConfiguration $configuration,
        CancellationTokenInterface $cancellation,
    ): Transaction {
        $session->acquireStream();

        if ($session->isClosed()) {
            $session->releaseStream();
            throw new RuntimeException('HTTP/2 connection is closed.');
        }

        $connection = $session->connection;
        $streamId = $connection->nextStreamId();
        $multiplexer = $session->multiplexer;

        $path = $request->requestTarget;
        $authority = $url->authority->host->toString();
        if ($url->authority->port !== null) {
            $authority .= ':' . $url->authority->port;
        }

        /** @var list<Header> $headers */
        $headers = [
            new Header(':method', $request->method),
            new Header(':path', $path),
            new Header(':scheme', $url->scheme),
            new Header(':authority', $authority),
        ];

        foreach ($request->headers->getIterator() as [$name, $value]) {
            $lower = strtolower($name);
            if ($lower === 'host' || $lower === 'connection' || $lower === 'transfer-encoding') {
                continue;
            }

            $headers[] = new Header($lower, $value);
        }

        if ($request->body === null && $request->trailers !== null) {
            throw RequestException::forInvalidRequest(
                'Trailers cannot be sent without a message body (RFC 9110 Section 6.5).',
            );
        }

        $stream = new H2Stream($streamId);
        $multiplexer->register($stream);

        try {
            if ($request->body !== null) {
                $connection->sendHeaders($streamId, $headers, false);
                self::sendBody($connection, $streamId, $request->body, $request->trailers === null);

                if ($request->trailers !== null) {
                    $trailerFields = $request->trailers->await();
                    /** @var list<Header> $trailerHeaders */
                    $trailerHeaders = [];
                    foreach ($trailerFields as [$name, $value]) {
                        $trailerHeaders[] = new Header(strtolower($name), $value);
                    }

                    $connection->sendHeaders($streamId, $trailerHeaders, true);
                }
            } else {
                $connection->sendHeaders($streamId, $headers, true);
            }

            [$status, $responseHeaders, $endStream] = $stream->awaitResponse($cancellation);
            $informational = $stream->getInformationalResponses();

            if ($endStream) {
                $multiplexer->unregister($streamId);
                $session->releaseStream();

                return new Transaction(
                    $informational,
                    null,
                    new Response(status: $status, protocolVersion: ProtocolVersion::V20, headers: $responseHeaders),
                );
            }

            $session->releaseStream();

            $bodyHandle = new ResponseBodyHandle($stream, $multiplexer, $streamId, $configuration->maxResponseBodySize);

            return new Transaction(
                $informational,
                null,
                new Response(
                    status: $status,
                    protocolVersion: ProtocolVersion::V20,
                    headers: $responseHeaders,
                    body: $bodyHandle,
                ),
            );
        } catch (CancelledException $e) {
            $multiplexer->unregister($streamId);
            $session->releaseStream();
            throw $e;
        } catch (ExceptionInterface|RuntimeException|IO\Exception\RuntimeException $e) {
            $multiplexer->unregister($streamId);
            $session->releaseStream();
            $session->markClosed();
            throw $e;
        }
    }

    /**
     * @param positive-int $streamId
     */
    private static function sendBody(
        ClientConnectionInterface $connection,
        int $streamId,
        IO\ReadHandleInterface $body,
        bool $endStreamOnFinish = true,
    ): void {
        $sentEndStream = false;

        while (!$body->reachedEndOfDataSource()) {
            $chunk = $body->read(16_384);
            if ($chunk === '') {
                break;
            }

            $isLast = $body->reachedEndOfDataSource();
            $endStream = $endStreamOnFinish && $isLast;
            $connection->sendAllData($streamId, $chunk, $endStream);
            if ($endStream) {
                $sentEndStream = true;
            }
        }

        if ($endStreamOnFinish && !$sentEndStream) {
            $connection->sendData($streamId, '', endStream: true);
        }
    }
}
