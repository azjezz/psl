<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit;

use Closure;
use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\Async\TimeoutCancellationToken;
use Psl\DateTime\Duration;
use Psl\HTTP\Client\Client;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Connection\ConnectionMetadata;
use Psl\HTTP\Client\SendConfiguration;
use Psl\HTTP\Message\ProtocolVersion;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Response;
use Psl\TCP;

use function Psl\URL\parse;

final class CallbackIntegrationTest extends TestCase
{
    public function testOnConnectionCallbackFires(): void
    {
        [$client, $listener] = self::createClientWithServer(static function (TCP\StreamInterface $conn): void {
            $conn->read();
            $conn->writeAll("HTTP/1.1 200 OK\r\ncontent-length: 2\r\n\r\nok");
            $conn->close();
        });

        $received = null;
        try {
            $tx = $client->send(
                self::request($listener),
                new SendConfiguration(onConnection: static function (ConnectionMetadata $m) use (&$received): void {
                    $received = $m;
                }),
            );

            static::assertNotNull($received);
            static::assertInstanceOf(ConnectionMetadata::class, $received);
            static::assertSame(200, $tx->response->status);
        } finally {
            $listener->close();
        }
    }

    public function testOnConnectionCallbackNullDoesNotBreak(): void
    {
        [$client, $listener] = self::createClientWithServer(static function (TCP\StreamInterface $conn): void {
            $conn->read();
            $conn->writeAll("HTTP/1.1 200 OK\r\ncontent-length: 2\r\n\r\nok");
            $conn->close();
        });

        try {
            $tx = $client->send(self::request($listener), new SendConfiguration(onConnection: null));

            static::assertSame(200, $tx->response->status);
        } finally {
            $listener->close();
        }
    }

    public function testOnInformationalCallbackOnSendConfigOnly(): void
    {
        [$client, $listener] = self::createClientWithServer(static function (TCP\StreamInterface $conn): void {
            $conn->read();
            $conn->writeAll("HTTP/1.1 103 Early Hints\r\nLink: </style.css>; rel=preload\r\n\r\n");
            Async\sleep(Duration::milliseconds(50));
            $conn->writeAll("HTTP/1.1 200 OK\r\ncontent-length: 4\r\n\r\ndone");
            $conn->close();
        });

        $received = [];
        try {
            $tx = $client->send(
                self::request($listener),
                new SendConfiguration(onInformationalResponse: static function (Response $r) use (&$received): void {
                    $received[] = $r->status;
                }),
            );

            static::assertSame([103], $received);
            static::assertCount(1, $tx->informational);
            static::assertSame(103, $tx->informational[0]->status);
            static::assertSame(200, $tx->response->status);
        } finally {
            $listener->close();
        }
    }

    public function testOnInformationalCallbackOnClientConfigOnly(): void
    {
        $received = [];
        [$client, $listener] = self::createClientWithServer(static function (TCP\StreamInterface $conn): void {
            $conn->read();
            $conn->writeAll("HTTP/1.1 103 Early Hints\r\nLink: </a>\r\n\r\n");
            Async\sleep(Duration::milliseconds(50));
            $conn->writeAll("HTTP/1.1 200 OK\r\ncontent-length: 2\r\n\r\nok");
            $conn->close();
        }, onInformationalResponse: static function (Response $r) use (&$received): void {
            $received[] = $r->status;
        });

        try {
            $tx = $client->send(self::request($listener));

            static::assertSame([103], $received);
            static::assertCount(1, $tx->informational);
            static::assertSame(103, $tx->informational[0]->status);
        } finally {
            $listener->close();
        }
    }

    public function testOnInformationalCallbackBothClientAndSend(): void
    {
        $order = [];

        [$client, $listener] = self::createClientWithServer(static function (TCP\StreamInterface $conn): void {
            $conn->read();
            $conn->writeAll("HTTP/1.1 103 Early Hints\r\nLink: </a>\r\n\r\n");
            Async\sleep(Duration::milliseconds(50));
            $conn->writeAll("HTTP/1.1 200 OK\r\ncontent-length: 2\r\n\r\nok");
            $conn->close();
        }, onInformationalResponse: static function (Response $r) use (&$order): void {
            $order[] = 'client';
        });

        try {
            $tx = $client->send(
                self::request($listener),
                new SendConfiguration(onInformationalResponse: static function (Response $r) use (&$order): void {
                    $order[] = 'send';
                }),
            );

            static::assertSame(['client', 'send'], $order);
            static::assertCount(1, $tx->informational);
        } finally {
            $listener->close();
        }
    }

    public function testOnInformationalCallbackNeitherSet(): void
    {
        [$client, $listener] = self::createClientWithServer(static function (TCP\StreamInterface $conn): void {
            $conn->read();
            $conn->writeAll("HTTP/1.1 103 Early Hints\r\nLink: </a>\r\n\r\n");
            Async\sleep(Duration::milliseconds(50));
            $conn->writeAll("HTTP/1.1 200 OK\r\ncontent-length: 2\r\n\r\nok");
            $conn->close();
        });

        try {
            $tx = $client->send(self::request($listener));

            static::assertCount(1, $tx->informational);
            static::assertSame(103, $tx->informational[0]->status);
            static::assertSame(200, $tx->response->status);
        } finally {
            $listener->close();
        }
    }

    public function testMultipleInformationalResponsesCallbackOrder(): void
    {
        [$client, $listener] = self::createClientWithServer(static function (TCP\StreamInterface $conn): void {
            $conn->read();
            $conn->writeAll("HTTP/1.1 100 Continue\r\n\r\n");
            Async\sleep(Duration::milliseconds(30));
            $conn->writeAll("HTTP/1.1 103 Early Hints\r\nLink: </a>\r\n\r\n");
            Async\sleep(Duration::milliseconds(30));
            $conn->writeAll("HTTP/1.1 200 OK\r\ncontent-length: 4\r\n\r\ndone");
            $conn->close();
        });

        $received = [];
        try {
            $tx = $client->send(
                self::request($listener),
                new SendConfiguration(onInformationalResponse: static function (Response $r) use (&$received): void {
                    $received[] = $r->status;
                }),
            );

            static::assertSame([100, 103], $received);
            static::assertCount(2, $tx->informational);
            static::assertSame(100, $tx->informational[0]->status);
            static::assertSame(103, $tx->informational[1]->status);
            static::assertSame(200, $tx->response->status);
        } finally {
            $listener->close();
        }
    }

    public function testOnConnectionAndOnInformationalTogether(): void
    {
        [$client, $listener] = self::createClientWithServer(static function (TCP\StreamInterface $conn): void {
            $conn->read();
            $conn->writeAll("HTTP/1.1 103 Early Hints\r\nLink: </a>\r\n\r\n");
            Async\sleep(Duration::milliseconds(50));
            $conn->writeAll("HTTP/1.1 200 OK\r\ncontent-length: 2\r\n\r\nok");
            $conn->close();
        });

        $track = '';
        try {
            $tx = $client->send(
                self::request($listener),
                new SendConfiguration(onInformationalResponse: static function (Response $r) use (&$track): void {
                    $track .= 'I';
                }, onConnection: static function (ConnectionMetadata $m) use (&$track): void {
                    $track .= 'C';
                }),
            );

            static::assertSame('CI', $track);
            static::assertSame(200, $tx->response->status);
        } finally {
            $listener->close();
        }
    }

    public function testNoInformationalResponsesCallbackNotInvoked(): void
    {
        [$client, $listener] = self::createClientWithServer(static function (TCP\StreamInterface $conn): void {
            $conn->read();
            $conn->writeAll("HTTP/1.1 200 OK\r\ncontent-length: 2\r\n\r\nok");
            $conn->close();
        });

        $called = false;
        try {
            $tx = $client->send(
                self::request($listener),
                new SendConfiguration(onInformationalResponse: static function (Response $r) use (&$called): void {
                    $called = true;
                }),
            );

            static::assertFalse($called);
            static::assertCount(0, $tx->informational);
            static::assertSame(200, $tx->response->status);
        } finally {
            $listener->close();
        }
    }

    /**
     * @param Closure(TCP\StreamInterface): void $serverHandler
     * @param null|(Closure(Response): void)     $onInformationalResponse
     *
     * @return array{Client, TCP\ListenerInterface}
     */
    private static function createClientWithServer(
        Closure $serverHandler,
        null|Closure $onInformationalResponse = null,
    ): array {
        $listener = TCP\listen('127.0.0.1', 0, new TCP\ListenConfiguration(noDelay: true));

        Async\run(static function () use ($listener, $serverHandler): void {
            $conn = $listener->accept(new TimeoutCancellationToken(Duration::seconds(5)));
            $serverHandler($conn);
        })->ignore();

        $client = new Client(configuration: new ClientConfiguration(
            protocolVersions: [ProtocolVersion::V11],
            onInformationalResponse: $onInformationalResponse,
        ));

        return [$client, $listener];
    }

    private static function request(TCP\ListenerInterface $listener): Request
    {
        $address = $listener->getLocalAddress();

        return new Request(method: 'GET', url: parse('http://127.0.0.1:' . $address->port . '/'));
    }
}
