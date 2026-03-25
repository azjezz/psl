<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Internal;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Connection\ConnectionInterface;
use Psl\HTTP\Client\Handler\HandlerInterface;
use Psl\HTTP\Client\Middleware\MiddlewareInterface;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Transaction;

/**
 * Composes a {@see MiddlewareInterface} and a {@see HandlerInterface} into a single handler.
 *
 * Used to build the connection middleware chain: each middleware is wrapped
 * around the next handler, with {@see ExchangeHandler} at the bottom. When
 * {@see handle()} is called, the middleware's {@see MiddlewareInterface::process()}
 * method is invoked with the next handler, allowing the middleware to inspect,
 * modify, or short-circuit the exchange.
 *
 * @internal
 *
 * @see ExchangeHandler The terminal handler at the bottom of the chain.
 */
final readonly class MiddlewareHandler implements HandlerInterface
{
    /**
     * @param MiddlewareInterface $middleware The middleware to apply.
     * @param HandlerInterface $next The next handler in the chain.
     */
    public function __construct(
        private MiddlewareInterface $middleware,
        private HandlerInterface $next,
    ) {}

    /**
     * Invoke the middleware, passing the next handler for delegation.
     *
     * @inheritDoc
     */
    #[Override]
    public function handle(
        ConnectionInterface $connection,
        Request $request,
        ClientConfiguration $configuration,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): Transaction {
        return $this->middleware->process($connection, $request, $configuration, $this->next, $cancellation);
    }
}
