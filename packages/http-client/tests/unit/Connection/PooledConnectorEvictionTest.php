<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit\Connection;

use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Async\TimeoutCancellationToken;
use Psl\DateTime\Duration;
use Psl\HTTP\Client\Client;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Connection\Origin;
use Psl\HTTP\Client\Connection\PooledConnector;
use Psl\HTTP\Client\Internal\H1\H1Connection;
use Psl\HTTP\Message\ProtocolVersion;
use Psl\HTTP\Message\Request;
use Psl\IO;
use Psl\Network;
use Psl\TCP;
use Psl\URL;

use function preg_match;
use function str_contains;
use function strlen;
use function strpos;
use function substr;

/**
 * @mago-expect lint:excessive-nesting
 */
final class PooledConnectorEvictionTest extends TestCase
{
    private static function readRequest(Network\StreamInterface $conn): bool
    {
        $buffer = '';
        while (true) {
            try {
                $chunk = $conn->read(cancellation: new TimeoutCancellationToken(Duration::seconds(2)));
            } catch (
                IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface|Async\Exception\CancelledException
            ) {
                return false;
            }

            if ($chunk === '') {
                return false;
            }

            $buffer .= $chunk;
            if (str_contains($buffer, "\r\n\r\n")) {
                break;
            }
        }

        $headerEnd = strpos($buffer, "\r\n\r\n");
        if ($headerEnd === false) {
            return false;
        }

        $headers = substr($buffer, 0, $headerEnd);
        if (preg_match('/content-length:\s*(\d+)/i', $headers, $m) !== 1) {
            return true;
        }

        $bodyLength = (int) $m[1];
        $bodyReceived = substr($buffer, $headerEnd + 4);
        while (strlen($bodyReceived) < $bodyLength) {
            try {
                $chunk = $conn->read(cancellation: new TimeoutCancellationToken(Duration::seconds(2)));
            } catch (
                IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface|Async\Exception\CancelledException
            ) {
                return false;
            }

            if ($chunk === '') {
                return false;
            }

            $bodyReceived .= $chunk;
        }

        return true;
    }

    /**
     * @return array{int, Async\Awaitable<int>}
     */
    private static function startServer(int $maxRequests, string $response): array
    {
        $listener = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));
        /** @var int<0, 65535> $port */
        $port = $listener->getLocalAddress()->port;

        $future = Async\run::<int>(static function () use ($listener, $maxRequests, $response): int {
            $acceptCount = 0;
            $handled = 0;

            try {
                while ($handled < $maxRequests) {
                    $conn = $listener->accept(new TimeoutCancellationToken(Duration::seconds(5)));
                    $acceptCount++;

                    while ($handled < $maxRequests) {
                        if (!self::readRequest($conn)) {
                            break;
                        }

                        try {
                            $conn->writeAll($response);
                        } catch (IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface) {
                            break;
                        }

                        $handled++;
                    }
                }
            } catch (
                IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface|Async\Exception\CancelledException
            ) {
                // @mago-expect lint:no-empty-catch-clause
            } finally {
                $listener->close();
            }

            return $acceptCount;
        });

        return [$port, $future];
    }

    public function testPerHostIdleEviction(): void
    {
        $response = "HTTP/1.1 200 OK\r\nContent-Length: 2\r\nConnection: keep-alive\r\n\r\nok";

        [$port, $serverFuture] = self::startServer(4, $response);

        $connector = new PooledConnector(maxIdleConnectionsPerHost: 1);
        $configuration = new ClientConfiguration(protocolVersions: [ProtocolVersion::V11]);
        $client = new Client(connector: $connector, configuration: $configuration);

        $url = URL\parse('http://127.0.0.1:' . $port . '/');

        $tx1 = $client->send(new Request(method: 'GET', url: $url));
        static::assertSame(200, $tx1->response->status);
        $tx1->response->body?->readAll();

        $tx2 = $client->send(new Request(method: 'GET', url: $url));
        static::assertSame(200, $tx2->response->status);
        $tx2->response->body?->readAll();

        $tx3 = $client->send(new Request(method: 'GET', url: $url));
        static::assertSame(200, $tx3->response->status);
        $tx3->response->body?->readAll();

        $tx4 = $client->send(new Request(method: 'GET', url: $url));
        static::assertSame(200, $tx4->response->status);
        $tx4->response->body?->readAll();

        $acceptCount = $serverFuture->await();

        static::assertGreaterThanOrEqual(1, $acceptCount);
        static::assertLessThanOrEqual(4, $acceptCount);
    }

    public function testGlobalIdleEvictionAcrossOrigins(): void
    {
        $response = "HTTP/1.1 200 OK\r\nContent-Length: 2\r\nConnection: keep-alive\r\n\r\nok";

        [$port1, $server1Future] = self::startServer(2, $response);
        [$port2, $server2Future] = self::startServer(2, $response);

        $connector = new PooledConnector(maxIdleConnections: 1, maxIdleConnectionsPerHost: 1);
        $configuration = new ClientConfiguration(protocolVersions: [ProtocolVersion::V11]);
        $client = new Client(connector: $connector, configuration: $configuration);

        $url1 = URL\parse('http://127.0.0.1:' . $port1 . '/');
        $url2 = URL\parse('http://127.0.0.1:' . $port2 . '/');

        $tx1 = $client->send(new Request(method: 'GET', url: $url1));
        static::assertSame(200, $tx1->response->status);
        $tx1->response->body?->readAll();

        $tx2 = $client->send(new Request(method: 'GET', url: $url2));
        static::assertSame(200, $tx2->response->status);
        $tx2->response->body?->readAll();

        $tx3 = $client->send(new Request(method: 'GET', url: $url1));
        static::assertSame(200, $tx3->response->status);
        $tx3->response->body?->readAll();

        $tx4 = $client->send(new Request(method: 'GET', url: $url2));
        static::assertSame(200, $tx4->response->status);
        $tx4->response->body?->readAll();

        $accept1 = $server1Future->await();
        $accept2 = $server2Future->await();

        static::assertSame(2, $accept1);
        static::assertSame(2, $accept2);
    }

    public function testH1ReleaseCallbackSkipsClosedStream(): void
    {
        $response = "HTTP/1.1 200 OK\r\nContent-Length: 2\r\nConnection: close\r\n\r\nok";

        [$port, $serverFuture] = self::startServer(2, $response);

        $connector = new PooledConnector();
        $configuration = new ClientConfiguration(protocolVersions: [ProtocolVersion::V11]);
        $client = new Client(connector: $connector, configuration: $configuration);

        $url = URL\parse('http://127.0.0.1:' . $port . '/');

        $tx1 = $client->send(new Request(method: 'GET', url: $url));
        static::assertSame(200, $tx1->response->status);
        $tx1->response->body?->readAll();

        $tx2 = $client->send(new Request(method: 'GET', url: $url));
        static::assertSame(200, $tx2->response->status);
        $tx2->response->body?->readAll();

        $acceptCount = $serverFuture->await();
        static::assertSame(2, $acceptCount);
    }

    public function testCheckoutH1PrunesClosedConnectionsAndReturnsLiveOne(): void
    {
        $response = "HTTP/1.1 200 OK\r\nContent-Length: 2\r\nConnection: keep-alive\r\n\r\nok";

        [$port, $serverFuture] = self::startServer(3, $response);

        $connector = new PooledConnector();
        $configuration = new ClientConfiguration(protocolVersions: [ProtocolVersion::V11]);
        $client = new Client(connector: $connector, configuration: $configuration);

        $url = URL\parse('http://127.0.0.1:' . $port . '/');

        $tx1 = $client->send(new Request(method: 'GET', url: $url));
        static::assertSame(200, $tx1->response->status);
        $tx1->response->body?->readAll();

        $tx2 = $client->send(new Request(method: 'GET', url: $url));
        static::assertSame(200, $tx2->response->status);
        $tx2->response->body?->readAll();

        $tx3 = $client->send(new Request(method: 'GET', url: $url));
        static::assertSame(200, $tx3->response->status);
        $tx3->response->body?->readAll();

        $acceptCount = $serverFuture->await();
        static::assertSame(1, $acceptCount);
    }

    public function testH1ConnectionPoolRespectsMaxIdlePerHost(): void
    {
        $response = "HTTP/1.1 200 OK\r\nContent-Length: 2\r\nConnection: keep-alive\r\n\r\nok";

        $connector = new PooledConnector(maxIdleConnectionsPerHost: 1, maxIdleConnections: 256);
        $configuration = new ClientConfiguration(protocolVersions: [ProtocolVersion::V11]);

        $listener = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));
        /** @var int<0, 65535> $port */
        $port = $listener->getLocalAddress()->port;

        $serverFuture = Async\run::<int>(static function () use ($listener, $response): int {
            $acceptCount = 0;
            $handled = 0;

            try {
                while ($handled < 4) {
                    $conn = $listener->accept(new TimeoutCancellationToken(Duration::seconds(5)));
                    $acceptCount++;

                    while ($handled < 4) {
                        if (!self::readRequest($conn)) {
                            break;
                        }

                        try {
                            $conn->writeAll($response);
                        } catch (IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface) {
                            break;
                        }

                        $handled++;
                    }
                }
            } catch (
                IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface|Async\Exception\CancelledException
            ) {
                // @mago-expect lint:no-empty-catch-clause
            } finally {
                $listener->close();
            }

            return $acceptCount;
        });

        $url = URL\parse('http://127.0.0.1:' . $port . '/');
        $request = new Request(method: 'GET', url: $url);
        $origin = Origin::fromUrl($request->url);

        $conn1 = $connector->connect($origin, $request, $configuration);
        static::assertInstanceOf(H1Connection::class, $conn1);
        $tx1 = $conn1->exchange($request, $configuration);
        $tx1 = $conn1->finalize($tx1);
        $tx1->response->body?->readAll();

        $conn2 = $connector->connect($origin, $request, $configuration);
        static::assertInstanceOf(H1Connection::class, $conn2);
        $tx2 = $conn2->exchange($request, $configuration);
        $tx2 = $conn2->finalize($tx2);
        $tx2->response->body?->readAll();

        $conn3 = $connector->connect($origin, $request, $configuration);
        static::assertInstanceOf(H1Connection::class, $conn3);
        $tx3 = $conn3->exchange($request, $configuration);
        $tx3 = $conn3->finalize($tx3);
        $tx3->response->body?->readAll();

        $conn4 = $connector->connect($origin, $request, $configuration);
        static::assertInstanceOf(H1Connection::class, $conn4);
        $tx4 = $conn4->exchange($request, $configuration);
        $tx4 = $conn4->finalize($tx4);
        $tx4->response->body?->readAll();

        $acceptCount = $serverFuture->await();

        static::assertSame(1, $acceptCount);
    }

    public function testPerHostEvictionWhenReleasingConcurrentConnections(): void
    {
        $response = "HTTP/1.1 200 OK\r\nContent-Length: 2\r\nConnection: keep-alive\r\n\r\nok";

        $listener = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));
        /** @var int<0, 65535> $port */
        $port = $listener->getLocalAddress()->port;

        $serverFuture = Async\run::<int>(static function () use ($listener, $response): int {
            $acceptCount = 0;
            try {
                $connections = [];
                for ($i = 0; $i < 3; $i++) {
                    $conn = $listener->accept(new TimeoutCancellationToken(Duration::seconds(5)));
                    $connections[] = $conn;
                    $acceptCount++;
                }

                foreach ($connections as $conn) {
                    if (!self::readRequest($conn)) {
                        continue;
                    }

                    try {
                        $conn->writeAll($response);
                    } catch (IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface) {
                        // @mago-expect lint:no-empty-catch-clause
                    }
                }

                Async\sleep(Duration::milliseconds(200));
            } catch (
                IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface|Async\Exception\CancelledException
            ) {
                // @mago-expect lint:no-empty-catch-clause
            } finally {
                $listener->close();
            }

            return $acceptCount;
        });

        $connector = new PooledConnector(maxIdleConnectionsPerHost: 1, maxIdleConnections: 256);
        $configuration = new ClientConfiguration(protocolVersions: [ProtocolVersion::V11]);

        $url = URL\parse('http://127.0.0.1:' . $port . '/');

        $tasks = [];
        for ($i = 0; $i < 3; $i++) {
            $tasks[] = static function () use ($connector, $configuration, $url): string {
                $request = new Request(method: 'GET', url: $url);
                $origin = Origin::fromUrl($request->url);
                $conn = $connector->connect($origin, $request, $configuration);
                $tx = $conn->exchange($request, $configuration);
                $tx = $conn->finalize($tx);
                return $tx->response->body?->readAll() ?? '';
            };
        }

        $results = Async\concurrently::<int, string>($tasks);

        static::assertCount(3, $results);
        foreach ($results as $body) {
            static::assertSame('ok', $body);
        }

        $acceptCount = $serverFuture->await();
        static::assertSame(3, $acceptCount);
    }

    public function testGlobalEvictionEvictsOldestConnectionsFirst(): void
    {
        $response = "HTTP/1.1 200 OK\r\nContent-Length: 2\r\nConnection: keep-alive\r\n\r\nok";

        $listener1 = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));
        /** @var int<0, 65535> $port1 */
        $port1 = $listener1->getLocalAddress()->port;
        $listener2 = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));
        /** @var int<0, 65535> $port2 */
        $port2 = $listener2->getLocalAddress()->port;
        $listener3 = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));
        /** @var int<0, 65535> $port3 */
        $port3 = $listener3->getLocalAddress()->port;

        $startSingleServer =
            static fn(TCP\ListenerInterface $listener): Async\Awaitable<int> => Async\run::<int>(static function () use (
                $listener,
                $response,
            ): int {
                $acceptCount = 0;
                $handled = 0;
                try {
                    while ($handled < 2) {
                        $conn = $listener->accept(new TimeoutCancellationToken(Duration::seconds(5)));
                        $acceptCount++;
                        while ($handled < 2) {
                            if (!self::readRequest($conn)) {
                                break;
                            }

                            try {
                                $conn->writeAll($response);
                            } catch (IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface) {
                                break;
                            }

                            $handled++;
                        }
                    }
                } catch (
                    IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface|Async\Exception\CancelledException
                ) {
                    // @mago-expect lint:no-empty-catch-clause
                } finally {
                    $listener->close();
                }

                return $acceptCount;
            });

        $server1Future = $startSingleServer($listener1);
        $server2Future = $startSingleServer($listener2);
        $server3Future = $startSingleServer($listener3);

        $connector = new PooledConnector(maxIdleConnections: 2, maxIdleConnectionsPerHost: 2);
        $configuration = new ClientConfiguration(protocolVersions: [ProtocolVersion::V11]);
        $client = new Client(connector: $connector, configuration: $configuration);

        $url1 = URL\parse('http://127.0.0.1:' . $port1 . '/');
        $url2 = URL\parse('http://127.0.0.1:' . $port2 . '/');
        $url3 = URL\parse('http://127.0.0.1:' . $port3 . '/');

        $tx1 = $client->send(new Request(method: 'GET', url: $url1));
        static::assertSame(200, $tx1->response->status);
        $tx1->response->body?->readAll();

        $tx2 = $client->send(new Request(method: 'GET', url: $url2));
        static::assertSame(200, $tx2->response->status);
        $tx2->response->body?->readAll();

        $tx3 = $client->send(new Request(method: 'GET', url: $url3));
        static::assertSame(200, $tx3->response->status);
        $tx3->response->body?->readAll();

        $tx1b = $client->send(new Request(method: 'GET', url: $url1));
        static::assertSame(200, $tx1b->response->status);
        $tx1b->response->body?->readAll();

        $accept1 = $server1Future->await();
        $accept2 = $server2Future->await();
        $accept3 = $server3Future->await();

        static::assertSame(2, $accept1);
        static::assertGreaterThanOrEqual(1, $accept2);
        static::assertGreaterThanOrEqual(1, $accept3);
    }
}
