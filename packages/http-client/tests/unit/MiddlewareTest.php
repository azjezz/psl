<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit;

use LogicException;
use PHPUnit\Framework\TestCase;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\HTTP\Client\Client;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Connection\ConnectionInterface;
use Psl\HTTP\Client\Connection\ConnectionMetadata;
use Psl\HTTP\Client\Connection\ConnectorInterface;
use Psl\HTTP\Client\Connection\Origin;
use Psl\HTTP\Client\Handler\HandlerInterface;
use Psl\HTTP\Client\Middleware\MiddlewareInterface;
use Psl\HTTP\Message\FieldMap;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Response;
use Psl\HTTP\Message\Transaction;
use Psl\IO;
use Psl\Network;
use Psl\Network\Address;
use Psl\URL;

final class MiddlewareTest extends TestCase
{
    private static function createCapturingConnector(Request &$capturedRequest): ConnectorInterface
    {
        return new class($capturedRequest) implements ConnectorInterface {
            public function __construct(
                private Request &$captured,
            ) {}

            public function connect(
                Origin $origin,
                Request $request,
                ClientConfiguration $configuration,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): ConnectionInterface {
                return new class($this->captured) implements ConnectionInterface {
                    public ConnectionMetadata $metadata;

                    public function __construct(
                        private Request &$captured,
                    ) {
                        $this->metadata = new ConnectionMetadata(
                            Address::tcp('127.0.0.1', 12_345),
                            Address::tcp('93.184.216.34', 80),
                        );
                    }

                    public function exchange(
                        Request $request,
                        ClientConfiguration $configuration,
                        CancellationTokenInterface $cancellation = new NullCancellationToken(),
                    ): Transaction {
                        $this->captured = $request;

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
        };
    }

    private static function createStaticConnector(): ConnectorInterface
    {
        return new class() implements ConnectorInterface {
            public function connect(
                Origin $origin,
                Request $request,
                ClientConfiguration $configuration,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): ConnectionInterface {
                return new class() implements ConnectionInterface {
                    public ConnectionMetadata $metadata;

                    public function __construct()
                    {
                        $this->metadata = new ConnectionMetadata(
                            Address::tcp('127.0.0.1', 12_345),
                            Address::tcp('93.184.216.34', 80),
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
        };
    }

    public function testMiddlewareCanModifyRequest(): void
    {
        $capturedRequest = new Request(method: 'GET', url: URL\parse('http://example.com/'));

        $middleware = new class() implements MiddlewareInterface {
            public function process(
                ConnectionInterface $connection,
                Request $request,
                ClientConfiguration $configuration,
                HandlerInterface $handler,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): Transaction {
                $request = $request->withHeader('X-Modified', 'true');

                return $handler->handle($connection, $request, $configuration, $cancellation);
            }
        };

        $connector = self::createCapturingConnector($capturedRequest);
        $client = new Client(
            connector: $connector,
            configuration: new ClientConfiguration(),
            middleware: [$middleware],
        );

        $request = new Request(method: 'GET', url: URL\parse('http://example.com/'));
        $client->send($request);

        static::assertSame('true', $capturedRequest->headers->get('X-Modified'));
    }

    public function testMiddlewareCanModifyResponse(): void
    {
        $middleware = new class() implements MiddlewareInterface {
            public function process(
                ConnectionInterface $connection,
                Request $request,
                ClientConfiguration $configuration,
                HandlerInterface $handler,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): Transaction {
                $transaction = $handler->handle($connection, $request, $configuration, $cancellation);

                return new Transaction(
                    $transaction->informational,
                    $transaction->pushed,
                    $transaction->response->withHeader('X-After', 'true'),
                );
            }
        };

        $connector = self::createStaticConnector();
        $client = new Client(
            connector: $connector,
            configuration: new ClientConfiguration(),
            middleware: [$middleware],
        );

        $request = new Request(method: 'GET', url: URL\parse('http://example.com/'));
        $transaction = $client->send($request);

        static::assertSame('true', $transaction->response->headers->get('X-After'));
    }

    public function testMiddlewareOrderIsPreserved(): void
    {
        $capturedRequest = new Request(method: 'GET', url: URL\parse('http://example.com/'));

        $first = new class() implements MiddlewareInterface {
            public function process(
                ConnectionInterface $connection,
                Request $request,
                ClientConfiguration $configuration,
                HandlerInterface $handler,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): Transaction {
                $request = $request->withHeader('X-First', '1');

                return $handler->handle($connection, $request, $configuration, $cancellation);
            }
        };

        $second = new class() implements MiddlewareInterface {
            public function process(
                ConnectionInterface $connection,
                Request $request,
                ClientConfiguration $configuration,
                HandlerInterface $handler,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): Transaction {
                $request = $request->withHeader('X-Second', '2');

                return $handler->handle($connection, $request, $configuration, $cancellation);
            }
        };

        $connector = self::createCapturingConnector($capturedRequest);
        $client = new Client(
            connector: $connector,
            configuration: new ClientConfiguration(),
            middleware: [$first, $second],
        );

        $request = new Request(method: 'GET', url: URL\parse('http://example.com/'));
        $client->send($request);

        static::assertSame('1', $capturedRequest->headers->get('X-First'));
        static::assertSame('2', $capturedRequest->headers->get('X-Second'));
    }

    public function testMiddlewareCanShortCircuit(): void
    {
        $exchangeCalled = false;

        $connector = new class($exchangeCalled) implements ConnectorInterface {
            public function __construct(
                private bool &$exchangeCalled,
            ) {}

            public function connect(
                Origin $origin,
                Request $request,
                ClientConfiguration $configuration,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): ConnectionInterface {
                return new class($this->exchangeCalled) implements ConnectionInterface {
                    public ConnectionMetadata $metadata;

                    public function __construct(
                        private bool &$exchangeCalled,
                    ) {
                        $this->metadata = new ConnectionMetadata(
                            Address::tcp('127.0.0.1', 12_345),
                            Address::tcp('93.184.216.34', 80),
                        );
                    }

                    public function exchange(
                        Request $request,
                        ClientConfiguration $configuration,
                        CancellationTokenInterface $cancellation = new NullCancellationToken(),
                    ): Transaction {
                        $this->exchangeCalled = true;
                        throw new LogicException('should not be called');
                    }

                    public function finalize(Transaction $transaction): Transaction
                    {
                        return $transaction;
                    }
                };
            }
        };

        $middleware = new class() implements MiddlewareInterface {
            public function process(
                ConnectionInterface $connection,
                Request $request,
                ClientConfiguration $configuration,
                HandlerInterface $handler,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): Transaction {
                return new Transaction(
                    [],
                    null,
                    new Response(status: 403, headers: new FieldMap(), body: new IO\MemoryHandle('blocked')),
                );
            }
        };

        $client = new Client(
            connector: $connector,
            configuration: new ClientConfiguration(),
            middleware: [$middleware],
        );

        $request = new Request(method: 'GET', url: URL\parse('http://example.com/'));
        $transaction = $client->send($request);

        static::assertSame(403, $transaction->response->status);
        static::assertFalse($exchangeCalled);
    }

    public function testMiddlewareExecutionOrderMatters(): void
    {
        $capturedRequest = new Request(method: 'GET', url: URL\parse('http://example.com/'));

        $first = new class() implements MiddlewareInterface {
            public function process(
                ConnectionInterface $connection,
                Request $request,
                ClientConfiguration $configuration,
                HandlerInterface $handler,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): Transaction {
                $request = $request->withHeader('X-Order', 'first');

                return $handler->handle($connection, $request, $configuration, $cancellation);
            }
        };

        $second = new class() implements MiddlewareInterface {
            public function process(
                ConnectionInterface $connection,
                Request $request,
                ClientConfiguration $configuration,
                HandlerInterface $handler,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): Transaction {
                $request = $request->withHeader('X-Order', 'second');

                return $handler->handle($connection, $request, $configuration, $cancellation);
            }
        };

        $connector = self::createCapturingConnector($capturedRequest);
        $client = new Client(
            connector: $connector,
            configuration: new ClientConfiguration(),
            middleware: [$first, $second],
        );

        $request = new Request(method: 'GET', url: URL\parse('http://example.com/'));
        $client->send($request);

        static::assertSame('second', $capturedRequest->headers->get('X-Order'));
    }
}
