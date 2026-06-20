<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit\Connection;

use Closure;
use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Async\TimeoutCancellationToken;
use Psl\DateTime\Duration;
use Psl\H2;
use Psl\HPACK;
use Psl\HTTP\Client\Client;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Connection\Origin;
use Psl\HTTP\Client\Connection\PooledConnector;
use Psl\HTTP\Client\Exception\RequestException;
use Psl\HTTP\Client\Exception\RuntimeException;
use Psl\HTTP\Client\Internal\H2\H2Connection;
use Psl\HTTP\Message\ProtocolVersion;
use Psl\HTTP\Message\Request;
use Psl\IO;
use Psl\Network;
use Psl\TCP;
use Psl\URL;

use function count;

/**
 * @mago-expect lint:excessive-nesting
 */
final class PooledConnectorH2Test extends TestCase
{
    public function testH2SessionReusedForSecondRequest(): void
    {
        $acceptCount = 0;
        [$port, $serverFuture] = self::startH2CServer(static function (
            H2\ServerConnectionInterface $server,
            H2\Event\HeadersReceived $event,
        ): void {
            $server->sendHeadersWithStatus(
                $event->streamId,
                '200',
                [new HPACK\Header('content-type', 'text/plain')],
                endStream: false,
            );
            $server->sendData($event->streamId, 'hello', endStream: true);
        }, $acceptCount);

        $client = new Client(
            connector: new PooledConnector(),
            configuration: new ClientConfiguration(protocolVersions: [ProtocolVersion::V20]),
        );

        $url = URL\parse('http://127.0.0.1:' . $port . '/');

        $tx1 = $client->send(new Request(method: 'GET', url: $url));
        static::assertSame(200, $tx1->response->status);
        static::assertSame(ProtocolVersion::V20, $tx1->response->protocolVersion);
        $body1 = $tx1->response->body?->readAll() ?? '';
        static::assertSame('hello', $body1);

        $tx2 = $client->send(new Request(method: 'GET', url: $url));
        static::assertSame(200, $tx2->response->status);
        static::assertSame(ProtocolVersion::V20, $tx2->response->protocolVersion);
        $body2 = $tx2->response->body?->readAll() ?? '';
        static::assertSame('hello', $body2);

        $serverFuture->await();

        static::assertSame(1, $acceptCount);
    }

    public function testH2SessionReturnedDirectlyFromPool(): void
    {
        $connector = new PooledConnector();

        [$port, $serverFuture] = self::startH2CServer(static function (
            H2\ServerConnectionInterface $server,
            H2\Event\HeadersReceived $event,
        ): void {
            $server->sendHeadersWithStatus($event->streamId, '200', [], endStream: false);
            $server->sendData($event->streamId, 'ok', endStream: true);
        });

        $configuration = new ClientConfiguration(protocolVersions: [ProtocolVersion::V20]);
        $url = URL\parse('http://127.0.0.1:' . $port . '/');
        $request = new Request(method: 'GET', url: $url);
        $origin = Origin::fromUrl($request->url);

        $conn1 = $connector->connect($origin, $request, $configuration);
        static::assertInstanceOf(H2Connection::class, $conn1);

        $tx1 = $conn1->exchange($request, $configuration);
        static::assertSame(200, $tx1->response->status);
        $tx1->response->body?->readAll();

        $conn2 = $connector->connect($origin, $request, $configuration);
        static::assertInstanceOf(H2Connection::class, $conn2);

        $tx2 = $conn2->exchange($request, $configuration);
        static::assertSame(200, $tx2->response->status);
        $tx2->response->body?->readAll();

        $serverFuture->await();
    }

    public function testClosedH2SessionPrunedOnConnect(): void
    {
        [$port1, $server1Future] = self::startH2CServer(static function (
            H2\ServerConnectionInterface $server,
            H2\Event\HeadersReceived $event,
        ): void {
            $server->sendHeadersWithStatus($event->streamId, '200', [], endStream: false);
            $server->sendData($event->streamId, 'first', endStream: true);
        }, handleCount: 1);

        $connector = new PooledConnector();
        $configuration = new ClientConfiguration(protocolVersions: [ProtocolVersion::V20]);

        $url1 = URL\parse('http://127.0.0.1:' . $port1 . '/');
        $request1 = new Request(method: 'GET', url: $url1);
        $origin1 = Origin::fromUrl($request1->url);

        $conn1 = $connector->connect($origin1, $request1, $configuration);
        static::assertInstanceOf(H2Connection::class, $conn1);
        $tx1 = $conn1->exchange($request1, $configuration);
        static::assertSame(200, $tx1->response->status);
        $tx1->response->body?->readAll();

        $server1Future->await();

        Async\sleep(Duration::milliseconds(50));

        [$port2, $server2Future] = self::startH2CServer(static function (
            H2\ServerConnectionInterface $server,
            H2\Event\HeadersReceived $event,
        ): void {
            $server->sendHeadersWithStatus($event->streamId, '200', [], endStream: false);
            $server->sendData($event->streamId, 'second', endStream: true);
        });

        $url2 = URL\parse('http://127.0.0.1:' . $port2 . '/');
        $request2 = new Request(method: 'GET', url: $url2);
        $origin2 = Origin::fromUrl($request2->url);

        $conn2 = $connector->connect($origin2, $request2, $configuration);
        static::assertInstanceOf(H2Connection::class, $conn2);
        $tx2 = $conn2->exchange($request2, $configuration);
        static::assertSame(200, $tx2->response->status);
        $body2 = $tx2->response->body?->readAll() ?? '';
        static::assertSame('second', $body2);

        $server2Future->await();
    }

    public function testH2SessionEvictionWhenPoolLimitReached(): void
    {
        $connector = new PooledConnector(maxIdleConnections: 1);
        $configuration = new ClientConfiguration(protocolVersions: [ProtocolVersion::V20]);

        [$port1, $server1Future] = self::startH2CServer(static function (
            H2\ServerConnectionInterface $server,
            H2\Event\HeadersReceived $event,
        ): void {
            $server->sendHeadersWithStatus($event->streamId, '200', [], endStream: false);
            $server->sendData($event->streamId, 'a', endStream: true);
        });

        $url1 = URL\parse('http://127.0.0.1:' . $port1 . '/');
        $request1 = new Request(method: 'GET', url: $url1);
        $origin1 = Origin::fromUrl($request1->url);

        $conn1 = $connector->connect($origin1, $request1, $configuration);
        $tx1 = $conn1->exchange($request1, $configuration);
        static::assertSame(200, $tx1->response->status);
        $tx1->response->body?->readAll();

        [$port2, $server2Future] = self::startH2CServer(static function (
            H2\ServerConnectionInterface $server,
            H2\Event\HeadersReceived $event,
        ): void {
            $server->sendHeadersWithStatus($event->streamId, '200', [], endStream: false);
            $server->sendData($event->streamId, 'b', endStream: true);
        });

        $url2 = URL\parse('http://127.0.0.1:' . $port2 . '/');
        $request2 = new Request(method: 'GET', url: $url2);
        $origin2 = Origin::fromUrl($request2->url);

        $conn2 = $connector->connect($origin2, $request2, $configuration);
        $tx2 = $conn2->exchange($request2, $configuration);
        static::assertSame(200, $tx2->response->status);
        $body2 = $tx2->response->body?->readAll() ?? '';
        static::assertSame('b', $body2);

        $server1Future->await();
        $server2Future->await();
    }

    public function testH2ConcurrentRequestsShareSession(): void
    {
        $acceptCount = 0;
        [$port, $serverFuture] = self::startH2CServer(static function (
            H2\ServerConnectionInterface $server,
            H2\Event\HeadersReceived $event,
        ): void {
            $server->sendHeadersWithStatus($event->streamId, '200', [], endStream: false);
            $server->sendData($event->streamId, 'stream-' . $event->streamId, endStream: true);
        }, $acceptCount);

        $connector = new PooledConnector();
        $client = new Client(
            connector: $connector,
            configuration: new ClientConfiguration(protocolVersions: [ProtocolVersion::V20]),
        );

        $url = URL\parse('http://127.0.0.1:' . $port . '/');
        $tasks = [];
        for ($i = 0; $i < 3; $i++) {
            $tasks[] = static function () use ($client, $url): string {
                $tx = $client->send(new Request(method: 'GET', url: $url));
                return $tx->response->body?->readAll() ?? '';
            };
        }

        $results = Async\concurrently::<int, string>($tasks);

        static::assertCount(3, $results);
        foreach ($results as $body) {
            static::assertStringStartsWith('stream-', $body);
        }

        $serverFuture->await();

        static::assertSame(1, $acceptCount);
    }

    public function testH2ReconnectWithMissingUrlThrowsRequestException(): void
    {
        [$port, $serverFuture] = self::startH2CServer(static function (
            H2\ServerConnectionInterface $server,
            H2\Event\HeadersReceived $event,
        ): void {
            $server->sendHeadersWithStatus($event->streamId, '200', [], endStream: false);
            $server->sendData($event->streamId, 'ok', endStream: true);
        }, handleCount: 1);

        $connector = new PooledConnector();
        $configuration = new ClientConfiguration(protocolVersions: [ProtocolVersion::V20]);
        $url = URL\parse('http://127.0.0.1:' . $port . '/');
        $request = new Request(method: 'GET', url: $url);
        $origin = Origin::fromUrl($request->url);

        $connection = $connector->connect($origin, $request, $configuration);
        static::assertInstanceOf(H2Connection::class, $connection);

        $tx = $connection->exchange($request, $configuration);
        static::assertSame(200, $tx->response->status);
        $tx->response->body?->readAll();

        $serverFuture->await();
        Async\sleep(Duration::milliseconds(50));

        $requestWithoutUrl = new Request(method: 'GET', url: null);

        try {
            $connection->exchange($requestWithoutUrl, $configuration);
            static::fail('Expected RequestException or RuntimeException for missing URL during reconnect');
        } catch (RequestException|RuntimeException) {
            static::addToAssertionCount(1);
        }
    }

    /**
     * @return array{int, Async\Awaitable<void>}
     */
    private static function startH2CServer(Closure $handler, int &$acceptCount = 0, int $handleCount = 0): array
    {
        $listener = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));
        /** @var int<0, 65535> $port */
        $port = $listener->getLocalAddress()->port;

        $future = Async\run::<void>(static function () use ($listener, $handler, &$acceptCount, $handleCount): void {
            try {
                $conn = $listener->accept();
                $acceptCount++;
                $server = new H2\ServerConnection($conn);
                $server->readClientPreface();
                $server->initialize();

                /** @var array<int, true> $handled */
                $handled = [];

                while ($server->isConnected()) {
                    try {
                        $events = $server->readEvent(new TimeoutCancellationToken(Duration::milliseconds(200)));
                    } catch (Async\Exception\CancelledException) {
                        break;
                    }

                    foreach ($events as $event) {
                        if (!($event instanceof H2\Event\HeadersReceived && !isset($handled[$event->streamId]))) {
                            continue;
                        }

                        $handled[$event->streamId] = true;
                        Async\Scheduler::defer(static fn(): mixed => $handler($server, $event));

                        if ($handleCount > 0 && count($handled) >= $handleCount) {
                            return;
                        }
                    }
                }
            } catch (
                IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface|H2\Exception\ExceptionInterface|HPACK\Exception\ExceptionInterface
            ) {
                // @mago-expect lint:no-empty-catch-clause
            } finally {
                $listener->close();
            }
        });

        return [$port, $future];
    }
}
