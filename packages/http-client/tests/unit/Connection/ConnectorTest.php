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
use Psl\HTTP\Client\Connection\Connector;
use Psl\HTTP\Client\Connection\Origin;
use Psl\HTTP\Client\Internal\H1\H1Connection;
use Psl\HTTP\Client\Internal\H2\H2Connection;
use Psl\HTTP\Client\ProxyConfiguration;
use Psl\HTTP\Message\ProtocolVersion;
use Psl\HTTP\Message\Request;
use Psl\IO;
use Psl\Network;
use Psl\TCP;
use Psl\Unix;

use function explode;
use function file_exists;
use function getmypid;
use function Psl\URL\parse;
use function str_contains;
use function strpos;
use function substr;
use function unlink;

use const PHP_OS_FAMILY;

/**
 * @mago-expect lint:excessive-nesting
 */
final class ConnectorTest extends TestCase
{
    public function testConnectTcpCreatesH1Connection(): void
    {
        $listener = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));

        /** @var int<0, 65535> $port */
        $port = $listener->getLocalAddress()->port;

        $serverFuture = Async\run(static function () use ($listener): void {
            try {
                $conn = $listener->accept(new TimeoutCancellationToken(Duration::seconds(5)));
                $conn->close();
            } catch (
                IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface|Async\Exception\CancelledException
            ) {
                // @mago-expect lint:no-empty-catch-clause
            }
        });

        try {
            $connector = new Connector();
            $configuration = new ClientConfiguration(protocolVersions: [ProtocolVersion::V11]);
            $request = new Request(method: 'GET', url: parse("http://127.0.0.1:{$port}/"));

            $connection = $connector->connect(
                Origin::fromUrl($request->url),
                $request,
                $configuration,
                new TimeoutCancellationToken(Duration::seconds(5)),
            );

            static::assertInstanceOf(H1Connection::class, $connection);
            static::assertFalse($connection->isForwardProxy);
            static::assertNull($connection->proxyAuthorization);
        } finally {
            $listener->close();
            try {
                $serverFuture->await();
            } catch (
                IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface|Async\Exception\CancelledException
            ) {
                // @mago-expect lint:no-empty-catch-clause
            }
        }
    }

    public function testConnectTcpWithForwardProxyCreatesForwardProxyConnection(): void
    {
        $proxyListener = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));

        /** @var int<0, 65535> $proxyPort */
        $proxyPort = $proxyListener->getLocalAddress()->port;

        $serverFuture = Async\run(static function () use ($proxyListener): void {
            try {
                $conn = $proxyListener->accept(new TimeoutCancellationToken(Duration::seconds(5)));
                $conn->close();
            } catch (
                IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface|Async\Exception\CancelledException
            ) {
                // @mago-expect lint:no-empty-catch-clause
            }
        });

        try {
            $connector = new Connector();
            $configuration = new ClientConfiguration(
                protocolVersions: [ProtocolVersion::V11],
                proxyConfiguration: new ProxyConfiguration(
                    parse("http://127.0.0.1:{$proxyPort}"),
                    authorization: 'Basic dXNlcjpwYXNz',
                ),
            );

            $request = new Request(method: 'GET', url: parse('http://target.example.com:9090/'));

            $connection = $connector->connect(
                Origin::fromUrl($request->url),
                $request,
                $configuration,
                new TimeoutCancellationToken(Duration::seconds(5)),
            );

            static::assertInstanceOf(H1Connection::class, $connection);
            static::assertTrue($connection->isForwardProxy);
            static::assertSame('Basic dXNlcjpwYXNz', $connection->proxyAuthorization);
        } finally {
            $proxyListener->close();
            try {
                $serverFuture->await();
            } catch (
                IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface|Async\Exception\CancelledException
            ) {
                // @mago-expect lint:no-empty-catch-clause
            }
        }
    }

    public function testConnectTcpForwardProxyWithoutAuth(): void
    {
        $proxyListener = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));

        /** @var int<0, 65535> $proxyPort */
        $proxyPort = $proxyListener->getLocalAddress()->port;

        $serverFuture = Async\run(static function () use ($proxyListener): void {
            try {
                $conn = $proxyListener->accept(new TimeoutCancellationToken(Duration::seconds(5)));
                $conn->close();
            } catch (
                IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface|Async\Exception\CancelledException
            ) {
                // @mago-expect lint:no-empty-catch-clause
            }
        });

        try {
            $connector = new Connector();
            $configuration = new ClientConfiguration(
                protocolVersions: [ProtocolVersion::V11],
                proxyConfiguration: new ProxyConfiguration(parse("http://127.0.0.1:{$proxyPort}")),
            );

            $request = new Request(method: 'GET', url: parse('http://example.com/'));

            $connection = $connector->connect(
                Origin::fromUrl($request->url),
                $request,
                $configuration,
                new TimeoutCancellationToken(Duration::seconds(5)),
            );

            static::assertInstanceOf(H1Connection::class, $connection);
            static::assertTrue($connection->isForwardProxy);
            static::assertNull($connection->proxyAuthorization);
        } finally {
            $proxyListener->close();
            try {
                $serverFuture->await();
            } catch (
                IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface|Async\Exception\CancelledException
            ) {
                // @mago-expect lint:no-empty-catch-clause
            }
        }
    }

    public function testForwardProxyBypassedForSkippedHost(): void
    {
        $proxyListener = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));

        /** @var int<0, 65535> $proxyPort */
        $proxyPort = $proxyListener->getLocalAddress()->port;

        $proxyWasContacted = false;
        $proxyFuture = Async\run(static function () use ($proxyListener, &$proxyWasContacted): void {
            try {
                $proxyListener->accept(new TimeoutCancellationToken(Duration::milliseconds(500)));
                $proxyWasContacted = true;
            } catch (
                IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface|Async\Exception\CancelledException
            ) {
                // @mago-expect lint:no-empty-catch-clause
            }
        });

        $targetListener = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));

        /** @var int<0, 65535> $targetPort */
        $targetPort = $targetListener->getLocalAddress()->port;

        $targetFuture = Async\run(static function () use ($targetListener): void {
            try {
                $conn = $targetListener->accept(new TimeoutCancellationToken(Duration::seconds(5)));
                $conn->read(cancellation: new TimeoutCancellationToken(Duration::milliseconds(500)));
            } catch (
                IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface|Async\Exception\CancelledException
            ) {
                // @mago-expect lint:no-empty-catch-clause
            }
        });

        try {
            $connector = new Connector();
            $configuration = new ClientConfiguration(
                protocolVersions: [ProtocolVersion::V11],
                proxyConfiguration: new ProxyConfiguration(
                    parse("http://127.0.0.1:{$proxyPort}"),
                    skipProxyFor: ['127.0.0.1'],
                ),
            );

            $request = new Request(method: 'GET', url: parse("http://127.0.0.1:{$targetPort}/"));

            $connection = $connector->connect(
                Origin::fromUrl($request->url),
                $request,
                $configuration,
                new TimeoutCancellationToken(Duration::seconds(5)),
            );

            static::assertInstanceOf(H1Connection::class, $connection);
            static::assertFalse($connection->isForwardProxy);
        } finally {
            $proxyListener->close();
            $targetListener->close();
            try {
                $proxyFuture->await();
            } catch (
                IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface|Async\Exception\CancelledException
            ) {
                // @mago-expect lint:no-empty-catch-clause
            }

            try {
                $targetFuture->await();
            } catch (
                IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface|Async\Exception\CancelledException
            ) {
                // @mago-expect lint:no-empty-catch-clause
            }
        }

        static::assertFalse(
            $proxyWasContacted,
            'The proxy should NOT have been contacted when the host is in skipProxyFor',
        );
    }

    public function testForwardProxyEndToEndExchange(): void
    {
        $response = "HTTP/1.1 200 OK\r\nContent-Length: 7\r\n\r\nproxied";

        $proxyListener = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));

        /** @var int<0, 65535> $proxyPort */
        $proxyPort = $proxyListener->getLocalAddress()->port;

        $capturedRequestLine = '';
        $capturedHeaders = '';

        $serverFuture = Async\run(static function () use (
            $proxyListener,
            $response,
            &$capturedRequestLine,
            &$capturedHeaders,
        ): void {
            try {
                $conn = $proxyListener->accept(new TimeoutCancellationToken(Duration::seconds(5)));
                $buffer = '';
                while (!str_contains($buffer, "\r\n\r\n")) {
                    $chunk = $conn->read(cancellation: new TimeoutCancellationToken(Duration::seconds(2)));
                    if ($chunk === '') {
                        break;
                    }

                    $buffer .= $chunk;
                }

                $headerEnd = strpos($buffer, "\r\n\r\n");
                if ($headerEnd !== false) {
                    $headerBlock = substr($buffer, 0, $headerEnd);
                    $lines = explode("\r\n", $headerBlock);
                    $capturedRequestLine = $lines[0] ?? '';
                    $capturedHeaders = $headerBlock;
                }

                $conn->writeAll($response);
                $conn->close();
            } catch (
                IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface|Async\Exception\CancelledException
            ) {
                // @mago-expect lint:no-empty-catch-clause
            }
        });

        try {
            $client = new Client(
                connector: new Connector(),
                configuration: new ClientConfiguration(
                    protocolVersions: [ProtocolVersion::V11],
                    proxyConfiguration: new ProxyConfiguration(
                        parse("http://127.0.0.1:{$proxyPort}"),
                        authorization: 'Basic cHJveHk6cGFzcw==',
                    ),
                ),
            );

            $tx = $client->send(new Request(method: 'GET', url: parse('http://target.example.com/resource')));

            static::assertSame(200, $tx->response->status);
            $body = $tx->response->body;
            static::assertNotNull($body);
            static::assertSame('proxied', $body->readAll());
        } finally {
            $proxyListener->close();
            $serverFuture->await();
        }

        static::assertTrue(
            str_contains($capturedRequestLine, 'http://target.example.com/resource'),
            'Proxy should receive absolute-form request target, got: ' . $capturedRequestLine,
        );
        static::assertTrue(
            str_contains($capturedHeaders, 'proxy-authorization: Basic cHJveHk6cGFzcw=='),
            'Proxy should receive Proxy-Authorization header',
        );
    }

    public function testConnectUnixSocketH1(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            static::markTestSkipped('Unix sockets are not supported on Windows.');
        }

        $socketPath = '/tmp/psl-connector-test-' . getmypid() . '-h1.sock';

        try {
            $listener = Unix\listen($socketPath);
            $serverFuture = Async\run(static function () use ($listener): void {
                try {
                    $conn = $listener->accept();
                    $conn->read(cancellation: new TimeoutCancellationToken(Duration::seconds(5)));
                    $conn->writeAll("HTTP/1.1 200 OK\r\nContent-Length: 4\r\n\r\nunix");
                    $conn->close();
                } catch (IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface) {
                    // @mago-expect lint:no-empty-catch-clause
                } finally {
                    $listener->close();
                }
            });

            $client = new Client(
                connector: new Connector(),
                configuration: new ClientConfiguration(
                    protocolVersions: [ProtocolVersion::V11],
                    unixSocket: $socketPath,
                ),
            );

            $tx = $client->send(new Request(method: 'GET', url: parse('http://localhost/')));

            static::assertSame(200, $tx->response->status);
            $body = $tx->response->body?->readAll() ?? '';
            static::assertSame('unix', $body);

            $serverFuture->await();
        } finally {
            if (file_exists($socketPath)) {
                unlink($socketPath);
            }
        }
    }

    public function testForwardProxyDefaultPort80(): void
    {
        $proxyListener = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));

        /** @var int<0, 65535> $proxyPort */
        $proxyPort = $proxyListener->getLocalAddress()->port;

        $serverFuture = Async\run(static function () use ($proxyListener): void {
            try {
                $conn = $proxyListener->accept(new TimeoutCancellationToken(Duration::seconds(5)));
                $conn->close();
            } catch (
                IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface|Async\Exception\CancelledException
            ) {
                // @mago-expect lint:no-empty-catch-clause
            }
        });

        try {
            $connector = new Connector();
            $configuration = new ClientConfiguration(
                protocolVersions: [ProtocolVersion::V11],
                proxyConfiguration: new ProxyConfiguration(parse("http://127.0.0.1:{$proxyPort}")),
            );

            $request = new Request(method: 'GET', url: parse('http://example.com/'));

            $connection = $connector->connect(
                Origin::fromUrl($request->url),
                $request,
                $configuration,
                new TimeoutCancellationToken(Duration::seconds(5)),
            );

            static::assertInstanceOf(H1Connection::class, $connection);
            static::assertTrue($connection->isForwardProxy);
        } finally {
            $proxyListener->close();
            try {
                $serverFuture->await();
            } catch (
                IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface|Async\Exception\CancelledException
            ) {
                // @mago-expect lint:no-empty-catch-clause
            }
        }
    }

    public function testConnectUnixSocketH2PriorKnowledge(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            static::markTestSkipped('Unix sockets are not supported on Windows.');
        }

        $socketPath = '/tmp/psl-connector-test-' . getmypid() . '-h2.sock';

        try {
            $serverFuture = self::startH2UnixServer($socketPath, static function (
                H2\ServerConnectionInterface $server,
                H2\Event\HeadersReceived $event,
            ): void {
                $server->sendHeadersWithStatus(
                    $event->streamId,
                    '200',
                    [
                        new HPACK\Header('content-type', 'text/plain'),
                    ],
                    endStream: false,
                );
                $server->sendData($event->streamId, 'hello h2 connector', endStream: true);
            });

            $client = new Client(
                connector: new Connector(),
                configuration: new ClientConfiguration(
                    protocolVersions: [ProtocolVersion::V20],
                    unixSocket: $socketPath,
                ),
            );

            $tx = $client->send(new Request(method: 'GET', url: parse('http://localhost/')));

            static::assertSame(200, $tx->response->status);
            static::assertSame(ProtocolVersion::V20, $tx->response->protocolVersion);
            $body = $tx->response->body?->readAll() ?? '';
            static::assertSame('hello h2 connector', $body);

            $serverFuture->await();
        } finally {
            if (file_exists($socketPath)) {
                unlink($socketPath);
            }
        }
    }

    public function testConnectTcpH2PriorKnowledge(): void
    {
        $listener = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));

        /** @var int<0, 65535> $port */
        $port = $listener->getLocalAddress()->port;

        $serverFuture = Async\run(static function () use ($listener): void {
            try {
                $conn = $listener->accept(new TimeoutCancellationToken(Duration::seconds(5)));
                $server = new H2\ServerConnection($conn);
                $server->readClientPreface();
                $server->initialize();

                /** @var array<int, true> $handled */
                $handled = [];

                while ($server->isConnected()) {
                    try {
                        $events = $server->readEvent(new TimeoutCancellationToken(Duration::milliseconds(100)));
                    } catch (Async\Exception\CancelledException) {
                        break;
                    }

                    foreach ($events as $event) {
                        if (!($event instanceof H2\Event\HeadersReceived && !isset($handled[$event->streamId]))) {
                            continue;
                        }

                        $handled[$event->streamId] = true;
                        $server->sendHeadersWithStatus(
                            $event->streamId,
                            '200',
                            [new HPACK\Header('content-type', 'text/plain')],
                            endStream: false,
                        );
                        $server->sendData($event->streamId, 'h2c-tcp', endStream: true);
                    }
                }
            } catch (
                IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface|H2\Exception\ExceptionInterface|HPACK\Exception\ExceptionInterface
            ) {
                // @mago-expect lint:no-empty-catch-clause
            }
        });

        try {
            $connector = new Connector();
            $configuration = new ClientConfiguration(protocolVersions: [ProtocolVersion::V20]);
            $request = new Request(method: 'GET', url: parse("http://127.0.0.1:{$port}/"));

            $connection = $connector->connect(
                Origin::fromUrl($request->url),
                $request,
                $configuration,
                new TimeoutCancellationToken(Duration::seconds(5)),
            );

            static::assertInstanceOf(H2Connection::class, $connection);
        } finally {
            $listener->close();
            try {
                $serverFuture->await();
            } catch (
                IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface|Async\Exception\CancelledException|H2\Exception\ExceptionInterface|HPACK\Exception\ExceptionInterface
            ) {
                // @mago-expect lint:no-empty-catch-clause
            }
        }
    }

    /**
     * @param non-empty-string $socketPath
     *
     * @return Async\Awaitable<void>
     */
    private static function startH2UnixServer(string $socketPath, Closure $handler): Async\Awaitable
    {
        $listener = Unix\listen($socketPath);

        return Async\run(static function () use ($listener, $handler): void {
            try {
                $conn = $listener->accept();
                $server = new H2\ServerConnection($conn);
                $server->readClientPreface();
                $server->initialize();

                /** @var array<int, true> $handled */
                $handled = [];

                while ($server->isConnected()) {
                    try {
                        $events = $server->readEvent(new TimeoutCancellationToken(Duration::milliseconds(100)));
                    } catch (Async\Exception\CancelledException) {
                        break;
                    }

                    foreach ($events as $event) {
                        if (!($event instanceof H2\Event\HeadersReceived && !isset($handled[$event->streamId]))) {
                            continue;
                        }

                        $handled[$event->streamId] = true;
                        Async\Scheduler::defer(static fn(): mixed => $handler($server, $event));
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
    }
}
