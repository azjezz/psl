<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Internal\H1;

use Closure;
use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Connection\ConnectionInterface;
use Psl\HTTP\Client\Exception;
use Psl\HTTP\Client\Internal\PoolReleasingBodyHandle;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Transaction;
use Psl\IO;
use Psl\Network;
use Psl\TLS;

/**
 * HTTP/1.x connection wrapping a single TCP/TLS stream.
 *
 * Supports one request/response exchange at a time per RFC 9112. After an
 * exchange completes, the connection may be reused if the server indicates
 * keep-alive. When a pool release callback is provided, the underlying stream
 * is returned to the connection pool once the response body is fully consumed
 * (via {@see PoolReleasingBodyHandle}).
 *
 * @internal
 *
 * @see Transport Performs the actual HTTP/1.x wire exchange.
 * @see PoolReleasingBodyHandle Defers pool release until the body is consumed.
 */
final class H1Connection implements ConnectionInterface
{
    /**
     * Buffered reader for parsing HTTP/1.x response messages.
     */
    public IO\Reader $reader;

    /**
     * Whether the most recent exchange indicated connection keep-alive.
     */
    public bool $keepAlive = false;

    public Network\Address $localAddress {
        get => $this->stream->getLocalAddress();
    }

    public Network\Address $peerAddress {
        get => $this->stream->getPeerAddress();
    }

    public null|TLS\ConnectionState $tlsState {
        get => $this->stream instanceof TLS\StreamInterface ? $this->stream->getState() : null;
    }

    /**
     * @param Network\StreamInterface $stream The underlying TCP/TLS stream.
     * @param null|(Closure(Network\StreamInterface): void) $onRelease Called with the stream when the connection can be returned to the pool. Null if pooling is not used.
     */
    public function __construct(
        public readonly Network\StreamInterface $stream,
        private readonly null|Closure $onRelease = null,
    ) {
        $this->reader = new IO\Reader($stream);
    }

    /**
     * Execute an HTTP/1.x request/response exchange on this connection.
     *
     * Delegates to {@see Transport::exchange()} for the wire-level exchange,
     * then determines whether the connection can be reused (keep-alive). If a
     * pool release callback is configured and keep-alive is indicated, the
     * response body is wrapped in a {@see PoolReleasingBodyHandle} to defer
     * pool return until the body is fully consumed.
     *
     * @throws Exception\RequestException If the request URL is missing.
     * @throws Exception\ProtocolException If the response is malformed or exceeds size limits.
     * @throws IO\Exception\RuntimeException If an I/O error occurs.
     * @throws Network\Exception\RuntimeException If a network error occurs.
     * @throws CancelledException If the cancellation token fires.
     */
    #[Override]
    public function exchange(
        Request $request,
        ClientConfiguration $configuration,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): Transaction {
        $maxHeaderSize = $configuration->maxResponseHeaderSize;
        $url = $request->url ?? throw Exception\RequestException::forMissingUrl();

        [$transaction, $keepAlive] = Transport::exchange(
            $this,
            $request,
            $url,
            $maxHeaderSize,
            $configuration->maxResponseBodySize,
            $cancellation,
        );

        $this->keepAlive = $keepAlive;

        return $transaction;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function finalize(Transaction $transaction): Transaction
    {
        if (!$this->keepAlive || $this->onRelease === null) {
            return $transaction;
        }

        $body = $transaction->response->body;
        $stream = $this->stream;

        if ($body === null || $body->reachedEndOfDataSource()) {
            ($this->onRelease)($stream);

            return $transaction;
        }

        $onRelease = $this->onRelease;
        $wrappedBody = new PoolReleasingBodyHandle($body, static function (bool $fullyConsumed) use (
            $onRelease,
            $stream,
        ): void {
            if ($fullyConsumed) {
                $onRelease($stream);
            } else {
                $stream->close();
            }
        });

        return new Transaction(
            $transaction->informational,
            $transaction->pushed,
            $transaction->response->withBody($wrappedBody),
        );
    }
}
