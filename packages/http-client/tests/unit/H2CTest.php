<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit;

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
use Psl\TCP;
use Psl\URL;

use function str_repeat;
use function strlen;

/**
 * @mago-expect lint:excessive-nesting
 */
final class H2CTest extends TestCase
{
    public function testH2cSimpleGet(): void
    {
        [$port, $serverFuture] = self::startH2CServer(static function (
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
            $server->sendData($event->streamId, 'hello h2c', endStream: true);
        });

        $client = new Client(
            connector: new Connector(),
            configuration: new ClientConfiguration(protocolVersions: [ProtocolVersion::V20]),
        );

        $tx = $client->send(new Request(method: 'GET', url: URL\parse('http://127.0.0.1:' . $port . '/')));

        static::assertSame(200, $tx->response->status);
        static::assertSame(ProtocolVersion::V20, $tx->response->protocolVersion);
        $body = $tx->response->body?->readAll() ?? '';
        static::assertSame('hello h2c', $body);

        $serverFuture->await();
    }

    public function testH2cLargeBody(): void
    {
        $payload = str_repeat('X', 100_000);

        [$port, $serverFuture] = self::startH2CServer(static function (
            H2\ServerConnectionInterface $server,
            H2\Event\HeadersReceived $event,
        ) use ($payload): void {
            $server->sendHeadersWithStatus($event->streamId, '200', [], endStream: false);
            $server->sendAllData($event->streamId, $payload, endStream: true);
        });

        $client = new Client(
            connector: new Connector(),
            configuration: new ClientConfiguration(protocolVersions: [ProtocolVersion::V20]),
        );

        $tx = $client->send(new Request(method: 'GET', url: URL\parse('http://127.0.0.1:' . $port . '/')));

        $body = $tx->response->body?->readAll() ?? '';
        static::assertSame(strlen($payload), strlen($body));

        $serverFuture->await();
    }

    public function testH2cConcurrentRequests(): void
    {
        [$port, $serverFuture] = self::startH2CServer(static function (
            H2\ServerConnectionInterface $server,
            H2\Event\HeadersReceived $event,
        ): void {
            $server->sendHeadersWithStatus($event->streamId, '200', [], endStream: false);
            $server->sendData($event->streamId, 'stream-' . $event->streamId, endStream: true);
        });

        $client = new Client(
            connector: new PooledConnector(),
            configuration: new ClientConfiguration(protocolVersions: [ProtocolVersion::V20]),
        );

        $url = URL\parse('http://127.0.0.1:' . $port . '/');
        $tasks = [];
        for ($i = 0; $i < 5; $i++) {
            $tasks[] = static function () use ($client, $url): string {
                $tx = $client->send(new Request(method: 'GET', url: $url));
                return $tx->response->body?->readAll() ?? '';
            };
        }

        $results = Async\concurrently($tasks);

        static::assertCount(5, $results);
        foreach ($results as $body) {
            static::assertStringStartsWith('stream-', $body);
        }

        $serverFuture->await();
    }

    /**
     * @return array{int, Async\Awaitable<void>}
     */
    private static function startH2CServer(Closure $handler): array
    {
        $listener = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));
        /** @var int<0, 65535> $port */
        $port = $listener->getLocalAddress()->port;

        $future = Async\run(static function () use ($listener, $handler): void {
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

        return [$port, $future];
    }
}
