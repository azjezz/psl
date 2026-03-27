<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit;

use ArrayObject;
use PHPUnit\Framework\TestCase;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\DateTime\Duration;
use Psl\HTTP\Client\Client;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Connection\ConnectionInterface;
use Psl\HTTP\Client\Connection\ConnectionMetadata;
use Psl\HTTP\Client\Connection\ConnectorInterface;
use Psl\HTTP\Client\Connection\Origin;
use Psl\HTTP\Client\Exception\ProtocolException;
use Psl\HTTP\Client\Exception\RequestException;
use Psl\HTTP\Client\SendConfiguration;
use Psl\HTTP\Message\FieldMap;
use Psl\HTTP\Message\ProtocolVersion;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Response;
use Psl\HTTP\Message\Transaction;
use Psl\IO;
use Psl\Network;
use Psl\Network\Exception\RuntimeException;

use function Psl\HTTP\Client\Internal\resolve_protocol_versions;
use function Psl\URL\parse;

final class ClientTest extends TestCase
{
    public function testSendWithoutUrlAndWithoutBaseUrlThrowsRequestException(): void
    {
        $client = new Client(
            connector: $this->createStub(ConnectorInterface::class),
            configuration: new ClientConfiguration(),
        );

        $request = new Request(method: 'GET', url: null, requestTarget: '/path');

        $this->expectException(RequestException::class);
        $client->send($request);
    }

    public function testConnectorReceivesRequestWithResolvedUrl(): void
    {
        $connector = new class() implements ConnectorInterface {
            public null|Request $receivedRequest = null;

            public function connect(
                Origin $origin,
                Request $request,
                ClientConfiguration $configuration,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): ConnectionInterface {
                $this->receivedRequest = $request;
                throw new RuntimeException('test');
            }
        };

        $client = new Client(connector: $connector, configuration: new ClientConfiguration());

        $request = new Request(method: 'GET', url: parse('http://example.com/path?q=1'));

        try {
            $client->send($request);
        } catch (RuntimeException) {
            static::addToAssertionCount(1);
        }

        static::assertNotNull($connector->receivedRequest);
        static::assertNotNull($connector->receivedRequest->url);
        static::assertSame('http', $connector->receivedRequest->url->scheme);
        static::assertSame('/path?q=1', $connector->receivedRequest->requestTarget);
    }

    public function testSendWithHttp3ProtocolVersionThrowsProtocolException(): void
    {
        $connector = new class() implements ConnectorInterface {
            public function connect(
                Origin $origin,
                Request $request,
                ClientConfiguration $configuration,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): ConnectionInterface {
                resolve_protocol_versions($request, $configuration);

                throw new RuntimeException('should not reach here');
            }
        };

        $client = new Client(connector: $connector, configuration: new ClientConfiguration());

        $request = new Request(method: 'GET', url: parse('http://example.com/'), protocolVersion: ProtocolVersion::V30);

        $this->expectException(ProtocolException::class);
        $this->expectExceptionMessage('Unsupported protocol version');
        $client->send($request);
    }

    public function testTransportErrorPropagates(): void
    {
        $connector = new class() implements ConnectorInterface {
            public function connect(
                Origin $origin,
                Request $request,
                ClientConfiguration $configuration,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): ConnectionInterface {
                throw new RuntimeException('connection refused');
            }
        };

        $client = new Client(connector: $connector, configuration: new ClientConfiguration());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('connection refused');
        $client->send(new Request(method: 'GET', url: parse('http://example.com/')));
    }

    public function testBaseUrlResolvesRelativeTarget(): void
    {
        $connector = new class() implements ConnectorInterface {
            public null|Request $receivedRequest = null;

            public function connect(
                Origin $origin,
                Request $request,
                ClientConfiguration $configuration,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): ConnectionInterface {
                $this->receivedRequest = $request;
                throw new RuntimeException('short-circuit');
            }
        };

        $client = new Client(
            connector: $connector,
            configuration: new ClientConfiguration(baseUrl: parse('https://example.com')),
        );

        $request = new Request(method: 'GET', url: null, requestTarget: '/api/users');

        try {
            $client->send($request);
        } catch (RuntimeException) {
            static::addToAssertionCount(1);
        }

        static::assertNotNull($connector->receivedRequest);
        static::assertNotNull($connector->receivedRequest->url);
        static::assertSame('https', $connector->receivedRequest->url->scheme);
        static::assertSame('example.com', $connector->receivedRequest->url->authority?->toString());
        static::assertSame('/api/users', $connector->receivedRequest->url->path);
        static::assertSame('/api/users', $connector->receivedRequest->requestTarget);
    }

    public function testBaseUrlResolutionFailsThrowsRequestException(): void
    {
        $connector = new class() implements ConnectorInterface {
            public function connect(
                Origin $origin,
                Request $request,
                ClientConfiguration $configuration,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): ConnectionInterface {
                throw new RuntimeException('should not reach here');
            }
        };

        $client = new Client(
            connector: $connector,
            configuration: new ClientConfiguration(baseUrl: parse('https://example.com')),
        );

        $request = new Request(method: 'GET', url: null, requestTarget: '://');

        $this->expectException(RequestException::class);
        $client->send($request);
    }

    public function testSendConfigurationOverridesBaseUrl(): void
    {
        $connector = new class() implements ConnectorInterface {
            public null|Request $receivedRequest = null;

            public function connect(
                Origin $origin,
                Request $request,
                ClientConfiguration $configuration,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): ConnectionInterface {
                $this->receivedRequest = $request;
                throw new RuntimeException('short-circuit');
            }
        };

        $client = new Client(
            connector: $connector,
            configuration: new ClientConfiguration(baseUrl: parse('https://default.example.com')),
        );

        $request = new Request(method: 'GET', url: null, requestTarget: '/v2/resource');

        try {
            $client->send($request, new SendConfiguration(baseUrl: parse('https://override.example.com')));
        } catch (RuntimeException) {
            static::addToAssertionCount(1);
        }

        static::assertNotNull($connector->receivedRequest);
        static::assertNotNull($connector->receivedRequest->url);
        static::assertSame('https', $connector->receivedRequest->url->scheme);
        static::assertSame('override.example.com', $connector->receivedRequest->url->authority?->toString());
        static::assertSame('/v2/resource', $connector->receivedRequest->url->path);
    }

    public function testNoUrlAndNoBaseUrlThrows(): void
    {
        $connector = new class() implements ConnectorInterface {
            public function connect(
                Origin $origin,
                Request $request,
                ClientConfiguration $configuration,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): ConnectionInterface {
                throw new RuntimeException('should not reach here');
            }
        };

        $client = new Client(connector: $connector, configuration: new ClientConfiguration());

        $request = new Request(method: 'POST', url: null, requestTarget: '/some/path');

        $this->expectException(RequestException::class);
        $client->send($request);
    }

    public function testDefaultUserAgentIsInjected(): void
    {
        $capture = new ArrayObject();
        $connector = $this->createCapturingConnector($capture);

        $client = new Client(connector: $connector, configuration: new ClientConfiguration());
        $client->send(new Request(method: 'GET', url: parse('http://example.com/')));

        static::assertArrayHasKey('request', (array) $capture);
        $request = $capture['request'];
        static::assertTrue($request->headers->has('user-agent'));
        static::assertSame('php-standard-library/http-client', $request->headers->get('user-agent'));
    }

    public function testCustomUserAgentIsNotOverridden(): void
    {
        $capture = new ArrayObject();
        $connector = $this->createCapturingConnector($capture);

        $client = new Client(connector: $connector, configuration: new ClientConfiguration());
        $client->send(new Request(
            method: 'GET',
            url: parse('http://example.com/'),
            headers: FieldMap::from([['user-agent', 'my-custom-agent']]),
        ));

        static::assertArrayHasKey('request', (array) $capture);
        $request = $capture['request'];
        static::assertSame('my-custom-agent', $request->headers->get('user-agent'));
    }

    public function testCustomUserAgentIsNotOverridden2(): void
    {
        $capture = new ArrayObject();
        $connector = $this->createCapturingConnector($capture);

        $client = new Client(connector: $connector, configuration: new ClientConfiguration());
        $client->send(new Request(
            method: 'GET',
            url: parse('http://example.com/'),
            headers: FieldMap::from([['User-Agent', 'my-custom-agent']]),
        ));

        static::assertArrayHasKey('request', (array) $capture);
        $request = $capture['request'];
        static::assertSame(['my-custom-agent'], $request->headers->getAll('user-agent'));
    }

    public function testHeadRequestWithBodyIsAllowed(): void
    {
        $capture = new ArrayObject();
        $connector = $this->createCapturingConnector($capture);
        $client = new Client(connector: $connector, configuration: new ClientConfiguration());

        $client->send(new Request(
            method: 'HEAD',
            url: parse('http://example.com/'),
            body: new IO\MemoryHandle('data'),
        ));

        static::assertSame('HEAD', $capture['request']->method);
        static::assertNotNull($capture['request']->body);
    }

    public function testTraceRequestWithBodyThrows(): void
    {
        $client = new Client(
            connector: $this->createStub(ConnectorInterface::class),
            configuration: new ClientConfiguration(),
        );

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('TRACE requests must not include a body.');

        $client->send(new Request(
            method: 'TRACE',
            url: parse('http://example.com/'),
            body: new IO\MemoryHandle('data'),
        ));
    }

    public function testHeadRequestWithoutBodySucceeds(): void
    {
        $capture = new ArrayObject();
        $connector = $this->createCapturingConnector($capture);

        $client = new Client(connector: $connector, configuration: new ClientConfiguration());
        $tx = $client->send(new Request(method: 'HEAD', url: parse('http://example.com/')));

        static::assertSame(200, $tx->response->status);
    }

    private function createCapturingConnector(ArrayObject $capture): ConnectorInterface
    {
        return new class($capture) implements ConnectorInterface {
            public function __construct(
                private readonly ArrayObject $capture,
            ) {}

            public function connect(
                Origin $origin,
                Request $request,
                ClientConfiguration $configuration,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): ConnectionInterface {
                return new class($this->capture) implements ConnectionInterface {
                    public ConnectionMetadata $metadata {
                        get => new ConnectionMetadata(Network\Address::tcp(), Network\Address::tcp());
                    }

                    public function __construct(
                        private readonly ArrayObject $capture,
                    ) {}

                    public function exchange(
                        Request $request,
                        ClientConfiguration $configuration,
                        CancellationTokenInterface $cancellation = new NullCancellationToken(),
                    ): Transaction {
                        $this->capture['request'] = $request;

                        return new Transaction(
                            [],
                            null,
                            new Response(
                                status: 200,
                                protocolVersion: ProtocolVersion::V11,
                                headers: FieldMap::from([]),
                            ),
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

    public function testOnConnectionCallbackIsInvoked(): void
    {
        $capture = new ArrayObject();
        $connector = $this->createCapturingConnector($capture);
        $client = new Client(connector: $connector, configuration: new ClientConfiguration());

        $receivedMetadata = null;
        $client->send(
            new Request(method: 'GET', url: parse('http://example.com/')),
            new SendConfiguration(onConnection: static function (ConnectionMetadata $metadata) use (
                &$receivedMetadata,
            ): void {
                $receivedMetadata = $metadata;
            }),
        );

        static::assertNotNull($receivedMetadata);
        static::assertInstanceOf(ConnectionMetadata::class, $receivedMetadata);
    }

    public function testOnConnectionCallbackNotInvokedWhenNull(): void
    {
        $capture = new ArrayObject();
        $connector = $this->createCapturingConnector($capture);
        $client = new Client(connector: $connector, configuration: new ClientConfiguration());

        $client->send(
            new Request(method: 'GET', url: parse('http://example.com/')),
            new SendConfiguration(onConnection: null),
        );

        static::assertArrayHasKey('request', (array) $capture);
    }

    public function testInvalidRequestTargetWithBaseUrlThrowsWithPreviousException(): void
    {
        $connector = $this->createStub(ConnectorInterface::class);
        $config = new ClientConfiguration(baseUrl: parse('http://example.com/'));
        $client = new Client(connector: $connector, configuration: $config);

        $request = new Request(method: 'GET', url: null, requestTarget: 'bad://[invalid url');

        try {
            $client->send($request);
            static::fail('Expected RequestException');
        } catch (RequestException $e) {
            static::assertNotNull($e->getPrevious());
        }
    }

    public function testConnectionTimeoutCancelsSlowConnection(): void
    {
        $connector = new class() implements ConnectorInterface {
            public null|CancellationTokenInterface $receivedCancellation = null;

            public function connect(
                Origin $origin,
                Request $request,
                ClientConfiguration $configuration,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): ConnectionInterface {
                $this->receivedCancellation = $cancellation;

                $cancellation->throwIfCancelled();

                \Psl\Async\sleep(Duration::seconds(5));

                $cancellation->throwIfCancelled();

                throw new RuntimeException('should not reach here');
            }
        };

        $client = new Client(connector: $connector, configuration: new ClientConfiguration());

        $this->expectException(CancelledException::class);

        $client->send(
            new Request(method: 'GET', url: parse('http://example.com/')),
            new SendConfiguration(connectionTimeout: Duration::milliseconds(10)),
        );
    }

    public function testConnectionTimeoutPassesLinkedCancellationToConnector(): void
    {
        $capture = new ArrayObject();
        $connector = new class($capture) implements ConnectorInterface {
            public function __construct(
                private readonly ArrayObject $capture,
            ) {}

            public function connect(
                Origin $origin,
                Request $request,
                ClientConfiguration $configuration,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): ConnectionInterface {
                $this->capture['cancellation'] = $cancellation;

                throw new RuntimeException('short-circuit');
            }
        };

        $client = new Client(connector: $connector, configuration: new ClientConfiguration());

        try {
            $client->send(
                new Request(method: 'GET', url: parse('http://example.com/')),
                new SendConfiguration(connectionTimeout: Duration::seconds(30)),
            );
        } catch (RuntimeException) {
            static::addToAssertionCount(1);
        }

        static::assertArrayHasKey('cancellation', (array) $capture);
        static::assertInstanceOf(\Psl\Async\LinkedCancellationToken::class, $capture['cancellation']);
        static::assertTrue($capture['cancellation']->cancellable);
    }

    public function testNoConnectionTimeoutPassesOriginalCancellation(): void
    {
        $capture = new ArrayObject();
        $connector = new class($capture) implements ConnectorInterface {
            public function __construct(
                private readonly ArrayObject $capture,
            ) {}

            public function connect(
                Origin $origin,
                Request $request,
                ClientConfiguration $configuration,
                CancellationTokenInterface $cancellation = new NullCancellationToken(),
            ): ConnectionInterface {
                $this->capture['cancellation'] = $cancellation;

                throw new RuntimeException('short-circuit');
            }
        };

        $client = new Client(connector: $connector, configuration: new ClientConfiguration());
        $originalCancellation = new NullCancellationToken();

        try {
            $client->send(
                new Request(method: 'GET', url: parse('http://example.com/')),
                new SendConfiguration(),
                $originalCancellation,
            );
        } catch (RuntimeException) {
            static::addToAssertionCount(1);
        }

        static::assertSame($originalCancellation, $capture['cancellation']);
    }
}
