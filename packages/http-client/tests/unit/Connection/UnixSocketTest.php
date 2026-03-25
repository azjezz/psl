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
use Psl\HTTP\Client\Connection\PooledConnector;
use Psl\HTTP\Message\ProtocolVersion;
use Psl\HTTP\Message\Request;
use Psl\IO;
use Psl\Network;
use Psl\Unix;

use function file_exists;
use function getmypid;
use function Psl\URL\parse;
use function unlink;

/**
 * @mago-expect lint:excessive-nesting
 */
final class UnixSocketTest extends TestCase
{
    /**
     * @return non-empty-string
     */
    private static function socketPath(string $suffix = ''): string
    {
        return '/tmp/psl-test-' . getmypid() . $suffix . '.sock';
    }

    public function testPooledConnectorConnectsViaUnixSocket(): void
    {
        $socketPath = self::socketPath('-pooled-h1');

        try {
            $listener = Unix\listen($socketPath);
            $serverFuture = Async\run(static function () use ($listener): void {
                try {
                    $conn = $listener->accept();
                    // Read part of the request (enough to proceed)
                    $conn->read(cancellation: new TimeoutCancellationToken(Duration::seconds(5)));
                    // Send a minimal HTTP/1.1 response
                    $conn->writeAll("HTTP/1.1 200 OK\r\nContent-Length: 2\r\n\r\nok");
                    $conn->close();
                } catch (IO\Exception\ExceptionInterface|Network\Exception\ExceptionInterface) {
                    // @mago-expect lint:no-empty-catch-clause
                } finally {
                    $listener->close();
                }
            });

            $client = new Client(
                connector: new PooledConnector(),
                configuration: new ClientConfiguration(
                    protocolVersions: [ProtocolVersion::V11],
                    unixSocket: $socketPath,
                ),
            );

            $tx = $client->send(new Request(method: 'GET', url: parse('http://localhost/')));

            static::assertSame(200, $tx->response->status);
            $body = $tx->response->body?->readAll() ?? '';
            static::assertSame('ok', $body);

            $serverFuture->await();
        } finally {
            if (file_exists($socketPath)) {
                unlink($socketPath);
            }
        }
    }

    public function testConnectorConnectsViaUnixSocket(): void
    {
        $socketPath = self::socketPath('-connector-h1');

        try {
            $listener = Unix\listen($socketPath);
            $serverFuture = Async\run(static function () use ($listener): void {
                try {
                    $conn = $listener->accept();
                    // Read part of the request (enough to proceed)
                    $conn->read(cancellation: new TimeoutCancellationToken(Duration::seconds(5)));
                    // Send a minimal HTTP/1.1 response
                    $conn->writeAll("HTTP/1.1 200 OK\r\nContent-Length: 2\r\n\r\nok");
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
            static::assertSame('ok', $body);

            $serverFuture->await();
        } finally {
            if (file_exists($socketPath)) {
                unlink($socketPath);
            }
        }
    }

    public function testUnixSocketH2PriorKnowledge(): void
    {
        $socketPath = self::socketPath('-h2');

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
                $server->sendData($event->streamId, 'hello h2 unix', endStream: true);
            });

            $client = new Client(
                connector: new PooledConnector(),
                configuration: new ClientConfiguration(
                    protocolVersions: [ProtocolVersion::V20],
                    unixSocket: $socketPath,
                ),
            );

            $tx = $client->send(new Request(method: 'GET', url: parse('http://localhost/')));

            static::assertSame(200, $tx->response->status);
            static::assertSame(ProtocolVersion::V20, $tx->response->protocolVersion);
            $body = $tx->response->body?->readAll() ?? '';
            static::assertSame('hello h2 unix', $body);

            $serverFuture->await();
        } finally {
            if (file_exists($socketPath)) {
                unlink($socketPath);
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
