<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit\Middleware;

use PHPUnit\Framework\TestCase;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\CIDR;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Connection\ConnectionInterface;
use Psl\HTTP\Client\Connection\ConnectionMetadata;
use Psl\HTTP\Client\Exception\RuntimeException;
use Psl\HTTP\Client\Handler\HandlerInterface;
use Psl\HTTP\Client\Middleware\DeniedDestinationsMiddleware;
use Psl\HTTP\Message\FieldMap;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Response;
use Psl\HTTP\Message\Transaction;
use Psl\IO;
use Psl\IP;
use Psl\Network;
use Psl\Network\Address;

use function Psl\URL\parse;

final class DeniedDestinationsMiddlewareTest extends TestCase
{
    private static function createConnection(string $peerHost): ConnectionInterface
    {
        return new class($peerHost, 80) implements ConnectionInterface {
            public ConnectionMetadata $metadata;

            public function __construct(string $peerHost, int $peerPort)
            {
                $this->metadata = new ConnectionMetadata(
                    Address::tcp('127.0.0.1', 12_345),
                    Address::tcp($peerHost, $peerPort),
                );
            }

            public function exchange(
                Request $request,
                ClientConfiguration $configuration,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): Transaction {
                return new Transaction(
                    [],
                    null,
                    new Response(status: 200, headers: new FieldMap(), body: new IO\MemoryHandle('ok')),
                );
            }

            public function finalize(Transaction $transaction): Transaction
            {
                return $transaction;
            }
        };
    }

    private static function createHandler(): HandlerInterface
    {
        return new class() implements HandlerInterface {
            public function handle(
                ConnectionInterface $connection,
                Request $request,
                ClientConfiguration $configuration,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): Transaction {
                return $connection->exchange($request, $configuration, $cancellation);
            }
        };
    }

    public function testBlocksPrivateIPv4(): void
    {
        $middleware = DeniedDestinationsMiddleware::forPrivateNetworkRanges();
        $connection = self::createConnection('192.168.1.1');
        $request = new Request(method: 'GET', url: parse('http://example.com/'));
        $handler = self::createHandler();

        $this->expectException(RuntimeException::class);

        $middleware->process($connection, $request, new ClientConfiguration(), $handler);
    }

    public function testBlocksLoopback(): void
    {
        $middleware = DeniedDestinationsMiddleware::forPrivateNetworkRanges();
        $connection = self::createConnection('127.0.0.1');
        $request = new Request(method: 'GET', url: parse('http://example.com/'));
        $handler = self::createHandler();

        $this->expectException(RuntimeException::class);

        $middleware->process($connection, $request, new ClientConfiguration(), $handler);
    }

    public function testAllowsPublicIP(): void
    {
        $middleware = DeniedDestinationsMiddleware::forPrivateNetworkRanges();
        $connection = self::createConnection('93.184.216.34');
        $request = new Request(method: 'GET', url: parse('http://example.com/'));
        $handler = self::createHandler();

        $transaction = $middleware->process($connection, $request, new ClientConfiguration(), $handler);

        static::assertSame(200, $transaction->response->status);
    }

    public function testBlocksExactIP(): void
    {
        $middleware = new DeniedDestinationsMiddleware([
            IP\Address::v4('10.0.0.5'),
        ]);
        $connection = self::createConnection('10.0.0.5');
        $request = new Request(method: 'GET', url: parse('http://example.com/'));
        $handler = self::createHandler();

        $this->expectException(RuntimeException::class);

        $middleware->process($connection, $request, new ClientConfiguration(), $handler);
    }

    public function testAllowsNonMatchingIP(): void
    {
        $middleware = new DeniedDestinationsMiddleware([
            IP\Address::v4('10.0.0.5'),
        ]);
        $connection = self::createConnection('93.184.216.34');
        $request = new Request(method: 'GET', url: parse('http://example.com/'));
        $handler = self::createHandler();

        $transaction = $middleware->process($connection, $request, new ClientConfiguration(), $handler);

        static::assertSame(200, $transaction->response->status);
    }

    public function testBlocksCIDRRange(): void
    {
        $middleware = new DeniedDestinationsMiddleware([
            new CIDR\Block('172.16.0.0/12'),
        ]);
        $connection = self::createConnection('172.20.1.1');
        $request = new Request(method: 'GET', url: parse('http://example.com/'));
        $handler = self::createHandler();

        $this->expectException(RuntimeException::class);

        $middleware->process($connection, $request, new ClientConfiguration(), $handler);
    }
}
