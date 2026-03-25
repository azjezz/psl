<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Internal;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Connection\ConnectionInterface;
use Psl\HTTP\Client\Exception\RuntimeException;
use Psl\HTTP\Client\Handler\HandlerInterface;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Transaction;

/**
 * Terminal handler at the bottom of the connection middleware chain.
 *
 * Delegates directly to the connection's {@see ConnectionInterface::exchange()} method
 * without any additional processing. This is the innermost handler that actually
 * performs the HTTP exchange; all middleware wraps around it via
 * {@see MiddlewareHandler}.
 *
 * @internal
 *
 * @see MiddlewareHandler Wraps middleware around this terminal handler.
 * @see ConnectionInterface::exchange() The actual exchange method invoked.
 */
final readonly class ExchangeHandler implements HandlerInterface
{
    /**
     * Delegate the exchange to the connection.
     *
     * @throws RuntimeException If the exchange fails.
     * @throws CancelledException If the cancellation token fires.
     */
    #[Override]
    public function handle(
        ConnectionInterface $connection,
        Request $request,
        ClientConfiguration $configuration,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): Transaction {
        if (!$request->headers->has('user-agent')) {
            $request = $request->withHeaders($request->headers->with('user-agent', 'php-standard-library/http-client'));
        }

        return $connection->exchange($request, $configuration, $cancellation);
    }
}
