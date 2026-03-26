<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Internal\H2;

use Closure;
use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\H2;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Connection\ConnectionInterface;
use Psl\HTTP\Client\Exception;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Transaction;
use Psl\IO;
use Psl\Network;
use Psl\TLS;

/**
 * HTTP/2 connection implementation backed by a multiplexed {@see H2Session}.
 *
 * When the underlying session is closed (e.g., server sent GOAWAY), the
 * connection transparently reconnects through the pool via the reconnect
 * closure, so callers never see session lifecycle errors.
 *
 * @internal
 */
final readonly class H2Connection implements ConnectionInterface
{
    /**
     * @param H2Session $session The shared HTTP/2 session.
     * @param Network\Address $localAddress Local socket address.
     * @param Network\Address $peerAddress Remote peer address.
     * @param null|TLS\ConnectionState $tlsState TLS state, or null for h2c.
     * @param null|(Closure(Request, ClientConfiguration, CancellationTokenInterface): ConnectionInterface) $reconnect Closure to obtain a fresh connection from the pool when this session dies.
     */
    public function __construct(
        private H2Session $session,
        public Network\Address $localAddress,
        public Network\Address $peerAddress,
        public null|TLS\ConnectionState $tlsState,
        private null|Closure $reconnect = null,
    ) {}

    /**
     * @throws Exception\RequestException If the request URL is missing.
     * @throws Exception\ProtocolException If the server sends a malformed response or a stream is reset.
     * @throws Exception\RuntimeException If the H2 connection is closed and reconnection fails.
     * @throws IO\Exception\RuntimeException If an I/O error occurs on the underlying stream.
     * @throws Network\Exception\RuntimeException If a network-level error occurs.
     * @throws CancelledException If the cancellation token fires.
     */
    #[Override]
    public function exchange(
        Request $request,
        ClientConfiguration $configuration,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): Transaction {
        if ($this->session->isClosed()) {
            return $this->reconnectAndExchange($request, $configuration, $cancellation);
        }

        $url = $request->url ?? throw Exception\RequestException::forMissingUrl();

        try {
            return StreamExchange::exchange($this->session, $request, $url, $configuration, $cancellation);
        } catch (CancelledException $e) {
            throw $e;
        } catch (Exception\RuntimeException|IO\Exception\RuntimeException|Network\Exception\RuntimeException $e) {
            if ($this->session->isClosed() && $this->reconnect !== null) {
                return $this->reconnectAndExchange($request, $configuration, $cancellation);
            }

            throw $e;
        } catch (H2\Exception\ExceptionInterface $e) {
            if ($this->session->isClosed() && $this->reconnect !== null) {
                return $this->reconnectAndExchange($request, $configuration, $cancellation);
            }

            throw new Exception\RuntimeException($e->getMessage(), previous: $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function finalize(Transaction $transaction): Transaction
    {
        return $transaction;
    }

    private function reconnectAndExchange(
        Request $request,
        ClientConfiguration $configuration,
        CancellationTokenInterface $cancellation,
    ): Transaction {
        if ($this->reconnect === null) {
            throw new Exception\RuntimeException('HTTP/2 connection is closed.');
        }

        $connection = ($this->reconnect)($request, $configuration, $cancellation);

        return $connection->exchange($request, $configuration, $cancellation);
    }
}
