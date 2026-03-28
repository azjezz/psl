<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../vendor/autoload.php';

use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\DateTime;
use Psl\HTTP\Client;
use Psl\HTTP\Client\Connection\ConnectionInterface;
use Psl\HTTP\Client\Handler\HandlerInterface;
use Psl\HTTP\Client\Middleware\MiddlewareInterface;
use Psl\HTTP\Message;
use Psl\URL;

// Middleware that delegates to the next handler (pass-through with logging)
final readonly class TimingMiddleware implements MiddlewareInterface
{
    public function process(
        ConnectionInterface $connection,
        Message\Request $request,
        Client\ClientConfiguration $configuration,
        HandlerInterface $handler,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): Message\Transaction {
        $start = DateTime\Timestamp::monotonic();

        // Delegate to the next handler. The handler calls finalize() internally.
        $transaction = $handler->handle($connection, $request, $configuration, $cancellation);

        $elapsed = DateTime\Timestamp::monotonic()->since($start)->toString();
        // Log: "GET https://example.com completed in 42.5ms"

        return $transaction;
    }
}

// Middleware that short-circuits the chain (returns a cached response)
final readonly class CacheMiddleware implements MiddlewareInterface
{
    public function __construct(
        /** @var array<string, Message\Response> */
        private array $cache = [],
    ) {}

    public function process(
        ConnectionInterface $connection,
        Message\Request $request,
        Client\ClientConfiguration $configuration,
        HandlerInterface $handler,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): Message\Transaction {
        $cacheKey = $request->method . ':' . $request->requestTarget;

        if (isset($this->cache[$cacheKey])) {
            // Short-circuit: MUST call finalize() to release the connection back to the pool
            return $connection->finalize(new Message\Transaction([], null, $this->cache[$cacheKey]));
        }

        // No cache hit, delegate to the next handler
        return $handler->handle($connection, $request, $configuration, $cancellation);
    }
}

$client = new Client\Client(middleware: [
    new TimingMiddleware(),
    new CacheMiddleware(),
]);

$tx = $client->send(new Message\Request(method: Message\METHOD_GET, url: URL\parse('https://example.com')));

$tx->response->status;
