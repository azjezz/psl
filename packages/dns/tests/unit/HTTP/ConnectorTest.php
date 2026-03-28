<?php

declare(strict_types=1);

namespace Psl\DNS\Tests\Unit\HTTP;

use Closure;
use PHPUnit\Framework\TestCase;
use Psl\Async;
use Psl\DateTime\Duration;
use Psl\DNS;
use Psl\DNS\Record\AAAARecord;
use Psl\DNS\Record\ARecord;
use Psl\DNS\Record\RecordType;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Connection\ConnectionInterface;
use Psl\HTTP\Client\Connection\ConnectionMetadata;
use Psl\HTTP\Client\Connection\ConnectorInterface;
use Psl\HTTP\Client\Connection\Origin;
use Psl\HTTP\Client\ProxyConfiguration;
use Psl\HTTP\Message\FieldMap;
use Psl\HTTP\Message\ProtocolVersion;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Response;
use Psl\HTTP\Message\Transaction;
use Psl\IP\Address;
use Psl\Network;

use function Psl\URL\parse;

final class ConnectorTest extends TestCase
{
    public function testResolvesHostnameAndDelegatesToInner(): void
    {
        $receivedOrigin = null;

        $inner = $this->createMockConnector(static function (Origin $origin) use (&$receivedOrigin): void {
            $receivedOrigin = $origin;
        });

        $resolver = new DNS\StaticResolver([
            'example.com' => [
                RecordType::A->value => [
                    new ARecord('example.com', Duration::seconds(300), Address::parse('1.2.3.4')),
                ],
            ],
        ]);

        $connector = new DNS\HTTP\Connector($inner, $resolver);

        $origin = new Origin('https', 'example.com', 443);
        $request = new Request(method: 'GET', url: parse('https://example.com/'));

        $connector->connect($origin, $request, new ClientConfiguration());

        static::assertNotNull($receivedOrigin);
        static::assertSame('1.2.3.4', $receivedOrigin->host);
        static::assertSame(443, $receivedOrigin->port);
        static::assertSame('https', $receivedOrigin->scheme);
    }

    public function testSetsOriginalHostnameAsSniHost(): void
    {
        $receivedOrigin = null;

        $inner = $this->createMockConnector(static function (Origin $origin) use (&$receivedOrigin): void {
            $receivedOrigin = $origin;
        });

        $resolver = new DNS\StaticResolver([
            'example.com' => [
                RecordType::A->value => [
                    new ARecord('example.com', Duration::seconds(300), Address::parse('1.2.3.4')),
                ],
            ],
        ]);

        $connector = new DNS\HTTP\Connector($inner, $resolver);
        $connector->connect(
            new Origin('https', 'example.com', 443),
            new Request(method: 'GET', url: parse('https://example.com/')),
            new ClientConfiguration(),
        );

        static::assertNotNull($receivedOrigin);
        static::assertSame('example.com', $receivedOrigin->sniHost);
    }

    public function testSkipsDnsForIpv4Address(): void
    {
        $receivedOrigin = null;

        $inner = $this->createMockConnector(static function (Origin $origin) use (&$receivedOrigin): void {
            $receivedOrigin = $origin;
        });

        $resolver = new DNS\StaticResolver([]);

        $connector = new DNS\HTTP\Connector($inner, $resolver);
        $connector->connect(
            new Origin('http', '127.0.0.1', 8080),
            new Request(method: 'GET', url: parse('http://127.0.0.1:8080/')),
            new ClientConfiguration(),
        );

        static::assertNotNull($receivedOrigin);
        static::assertSame('127.0.0.1', $receivedOrigin->host);
        static::assertNull($receivedOrigin->sniHost);
    }

    public function testSkipsDnsForIpv6Address(): void
    {
        $receivedOrigin = null;

        $inner = $this->createMockConnector(static function (Origin $origin) use (&$receivedOrigin): void {
            $receivedOrigin = $origin;
        });

        $resolver = new DNS\StaticResolver([]);

        $connector = new DNS\HTTP\Connector($inner, $resolver);
        $connector->connect(
            new Origin('http', '::1', 8080),
            new Request(method: 'GET', url: parse('http://[::1]:8080/')),
            new ClientConfiguration(),
        );

        static::assertNotNull($receivedOrigin);
        static::assertSame('::1', $receivedOrigin->host);
        static::assertNull($receivedOrigin->sniHost);
    }

    public function testFallsBackToAAAAWhenNoARecord(): void
    {
        $receivedOrigin = null;

        $inner = $this->createMockConnector(static function (Origin $origin) use (&$receivedOrigin): void {
            $receivedOrigin = $origin;
        });

        $resolver = new DNS\StaticResolver([
            'ipv6only.example.com' => [
                RecordType::AAAA->value => [
                    new AAAARecord('ipv6only.example.com', Duration::seconds(300), Address::parse('2001:db8::1')),
                ],
            ],
        ]);

        $connector = new DNS\HTTP\Connector($inner, $resolver);
        $connector->connect(
            new Origin('https', 'ipv6only.example.com', 443),
            new Request(method: 'GET', url: parse('https://ipv6only.example.com/')),
            new ClientConfiguration(),
        );

        static::assertNotNull($receivedOrigin);
        static::assertSame('[2001:db8::1]', $receivedOrigin->host);
        static::assertSame('ipv6only.example.com', $receivedOrigin->sniHost);
    }

    public function testIPv6ResolvedAddressIsWrappedInBrackets(): void
    {
        $receivedOrigin = null;

        $inner = $this->createMockConnector(static function (Origin $origin) use (&$receivedOrigin): void {
            $receivedOrigin = $origin;
        });

        $resolver = new DNS\StaticResolver([
            'v6.example.com' => [
                RecordType::AAAA->value => [
                    new AAAARecord('v6.example.com', Duration::seconds(300), Address::parse('::1')),
                ],
            ],
        ]);

        $connector = new DNS\HTTP\Connector($inner, $resolver);
        $connector->connect(
            new Origin('http', 'v6.example.com', 8080),
            new Request(method: 'GET', url: parse('http://v6.example.com:8080/')),
            new ClientConfiguration(),
        );

        static::assertNotNull($receivedOrigin);
        static::assertSame('[::1]', $receivedOrigin->host);
        static::assertSame(8080, $receivedOrigin->port);
        static::assertSame('v6.example.com', $receivedOrigin->sniHost);
    }

    public function testIPv4ResolvedAddressIsNotWrapped(): void
    {
        $receivedOrigin = null;

        $inner = $this->createMockConnector(static function (Origin $origin) use (&$receivedOrigin): void {
            $receivedOrigin = $origin;
        });

        $resolver = new DNS\StaticResolver([
            'v4.example.com' => [
                RecordType::A->value => [
                    new ARecord('v4.example.com', Duration::seconds(300), Address::parse('10.0.0.1')),
                ],
            ],
        ]);

        $connector = new DNS\HTTP\Connector($inner, $resolver);
        $connector->connect(
            new Origin('http', 'v4.example.com', 8080),
            new Request(method: 'GET', url: parse('http://v4.example.com:8080/')),
            new ClientConfiguration(),
        );

        static::assertNotNull($receivedOrigin);
        static::assertSame('10.0.0.1', $receivedOrigin->host);
        static::assertStringNotContainsString('[', $receivedOrigin->host);
    }

    public function testPrefersAOverAAAA(): void
    {
        $receivedOrigin = null;

        $inner = $this->createMockConnector(static function (Origin $origin) use (&$receivedOrigin): void {
            $receivedOrigin = $origin;
        });

        $resolver = new DNS\StaticResolver([
            'dual.example.com' => [
                RecordType::A->value => [
                    new ARecord('dual.example.com', Duration::seconds(300), Address::parse('10.0.0.1')),
                ],
                RecordType::AAAA->value => [
                    new AAAARecord('dual.example.com', Duration::seconds(300), Address::parse('2001:db8::1')),
                ],
            ],
        ]);

        $connector = new DNS\HTTP\Connector($inner, $resolver);
        $connector->connect(
            new Origin('https', 'dual.example.com', 443),
            new Request(method: 'GET', url: parse('https://dual.example.com/')),
            new ClientConfiguration(),
        );

        static::assertNotNull($receivedOrigin);
        static::assertSame('10.0.0.1', $receivedOrigin->host);
    }

    public function testThrowsWhenHostnameCannotBeResolved(): void
    {
        $inner = $this->createMockConnector(static function (): void {});

        $resolver = new DNS\StaticResolver([]);

        $connector = new DNS\HTTP\Connector($inner, $resolver);

        $this->expectException(Network\Exception\RuntimeException::class);
        $this->expectExceptionMessage('DNS resolution failed for "nonexistent.example.com"');

        $connector->connect(
            new Origin('https', 'nonexistent.example.com', 443),
            new Request(method: 'GET', url: parse('https://nonexistent.example.com/')),
            new ClientConfiguration(),
        );
    }

    public function testPreservesPortAndScheme(): void
    {
        $receivedOrigin = null;

        $inner = $this->createMockConnector(static function (Origin $origin) use (&$receivedOrigin): void {
            $receivedOrigin = $origin;
        });

        $resolver = new DNS\StaticResolver([
            'custom.example.com' => [
                RecordType::A->value => [
                    new ARecord('custom.example.com', Duration::seconds(60), Address::parse('192.168.1.1')),
                ],
            ],
        ]);

        $connector = new DNS\HTTP\Connector($inner, $resolver);
        $connector->connect(
            new Origin('http', 'custom.example.com', 9090),
            new Request(method: 'GET', url: parse('http://custom.example.com:9090/')),
            new ClientConfiguration(),
        );

        static::assertNotNull($receivedOrigin);
        static::assertSame('http', $receivedOrigin->scheme);
        static::assertSame('192.168.1.1', $receivedOrigin->host);
        static::assertSame(9090, $receivedOrigin->port);
        static::assertSame('custom.example.com', $receivedOrigin->sniHost);
    }

    public function testPassesRequestAndConfigToInner(): void
    {
        $receivedRequest = null;
        $receivedConfig = null;

        $inner = $this->createMockConnector(static function (Origin $o, Request $r, ClientConfiguration $c) use (
            &$receivedRequest,
            &$receivedConfig,
        ): void {
            $receivedRequest = $r;
            $receivedConfig = $c;
        });

        $resolver = new DNS\StaticResolver([
            'example.com' => [
                RecordType::A->value => [
                    new ARecord('example.com', Duration::seconds(300), Address::parse('1.1.1.1')),
                ],
            ],
        ]);

        $connector = new DNS\HTTP\Connector($inner, $resolver);
        $request = new Request(method: 'POST', url: parse('https://example.com/api'));
        $config = new ClientConfiguration(protocolVersions: [ProtocolVersion::V11]);

        $connector->connect(new Origin('https', 'example.com', 443), $request, $config);

        static::assertSame($request, $receivedRequest);
        static::assertSame($config, $receivedConfig);
    }

    public function testResolvesHttpProxyHostname(): void
    {
        $receivedConfig = null;

        $inner = $this->createMockConnector(static function (Origin $o, Request $r, ClientConfiguration $c) use (
            &$receivedConfig,
        ): void {
            $receivedConfig = $c;
        });

        $resolver = new DNS\StaticResolver([
            'example.com' => [
                RecordType::A->value => [
                    new ARecord('example.com', Duration::seconds(300), Address::parse('1.2.3.4')),
                ],
            ],
            'myproxy.local' => [
                RecordType::A->value => [
                    new ARecord('myproxy.local', Duration::seconds(300), Address::parse('10.0.0.99')),
                ],
            ],
        ]);

        $connector = new DNS\HTTP\Connector($inner, $resolver);
        $config = new ClientConfiguration(proxyConfiguration: new ProxyConfiguration(parse(
            'http://myproxy.local:3128',
        )));

        $connector->connect(
            new Origin('http', 'example.com', 80),
            new Request(method: 'GET', url: parse('http://example.com/')),
            $config,
        );

        static::assertNotNull($receivedConfig);
        static::assertNotNull($receivedConfig->proxyConfiguration);
        static::assertSame('10.0.0.99', $receivedConfig->proxyConfiguration->url->authority->host->toString());
        static::assertSame(3128, $receivedConfig->proxyConfiguration->url->authority->port);
        static::assertSame('myproxy.local', $receivedConfig->proxyConfiguration->sni);
    }

    public function testPreservesProxyCredentialsAfterResolve(): void
    {
        $receivedConfig = null;

        $inner = $this->createMockConnector(static function (Origin $o, Request $r, ClientConfiguration $c) use (
            &$receivedConfig,
        ): void {
            $receivedConfig = $c;
        });

        $resolver = new DNS\StaticResolver([
            'example.com' => [
                RecordType::A->value => [
                    new ARecord('example.com', Duration::seconds(300), Address::parse('1.2.3.4')),
                ],
            ],
            'proxy.local' => [
                RecordType::A->value => [
                    new ARecord('proxy.local', Duration::seconds(300), Address::parse('10.0.0.1')),
                ],
            ],
        ]);

        $connector = new DNS\HTTP\Connector($inner, $resolver);
        $config = new ClientConfiguration(
            proxyConfiguration: new ProxyConfiguration(
                parse('http://proxy.local:8080'),
                authorization: 'Basic dXNlcjpwYXNz',
            ),
        );

        $connector->connect(
            new Origin('http', 'example.com', 80),
            new Request(method: 'GET', url: parse('http://example.com/')),
            $config,
        );

        static::assertNotNull($receivedConfig);
        static::assertNotNull($receivedConfig->proxyConfiguration);
        static::assertSame('10.0.0.1', $receivedConfig->proxyConfiguration->url->authority->host->toString());
        static::assertSame(8080, $receivedConfig->proxyConfiguration->url->authority->port);
        static::assertSame('Basic dXNlcjpwYXNz', $receivedConfig->proxyConfiguration->authorization);
        static::assertSame('proxy.local', $receivedConfig->proxyConfiguration->sni);
    }

    public function testResolvesHttpsProxyHostname(): void
    {
        $receivedConfig = null;

        $inner = $this->createMockConnector(static function (Origin $o, Request $r, ClientConfiguration $c) use (
            &$receivedConfig,
        ): void {
            $receivedConfig = $c;
        });

        $resolver = new DNS\StaticResolver([
            'example.com' => [
                RecordType::A->value => [
                    new ARecord('example.com', Duration::seconds(300), Address::parse('1.2.3.4')),
                ],
            ],
            'secure-proxy.example.com' => [
                RecordType::A->value => [
                    new ARecord('secure-proxy.example.com', Duration::seconds(300), Address::parse('10.0.0.5')),
                ],
            ],
        ]);

        $connector = new DNS\HTTP\Connector($inner, $resolver);
        $config = new ClientConfiguration(proxyConfiguration: new ProxyConfiguration(parse(
            'https://secure-proxy.example.com:443',
        )));

        $connector->connect(
            new Origin('http', 'example.com', 80),
            new Request(method: 'GET', url: parse('http://example.com/')),
            $config,
        );

        static::assertNotNull($receivedConfig);
        static::assertNotNull($receivedConfig->proxyConfiguration);
        static::assertSame('10.0.0.5', $receivedConfig->proxyConfiguration->url->authority->host->toString());
        static::assertSame('https', $receivedConfig->proxyConfiguration->url->scheme);
        static::assertSame('secure-proxy.example.com', $receivedConfig->proxyConfiguration->sni);
    }

    public function testDoesNotResolveProxyWithIpAddress(): void
    {
        $receivedConfig = null;

        $inner = $this->createMockConnector(static function (Origin $o, Request $r, ClientConfiguration $c) use (
            &$receivedConfig,
        ): void {
            $receivedConfig = $c;
        });

        $resolver = new DNS\StaticResolver([
            'example.com' => [
                RecordType::A->value => [
                    new ARecord('example.com', Duration::seconds(300), Address::parse('1.2.3.4')),
                ],
            ],
        ]);

        $connector = new DNS\HTTP\Connector($inner, $resolver);
        $proxy = new ProxyConfiguration(parse('http://10.0.0.1:3128'));
        $config = new ClientConfiguration(proxyConfiguration: $proxy);

        $connector->connect(
            new Origin('http', 'example.com', 80),
            new Request(method: 'GET', url: parse('http://example.com/')),
            $config,
        );

        static::assertNotNull($receivedConfig);
        static::assertSame($proxy, $receivedConfig->proxyConfiguration);
    }

    public function testResolvesSocksProxyHostname(): void
    {
        $receivedConfig = null;

        $inner = $this->createMockConnector(static function (Origin $o, Request $r, ClientConfiguration $c) use (
            &$receivedConfig,
        ): void {
            $receivedConfig = $c;
        });

        $resolver = new DNS\StaticResolver([
            'example.com' => [
                RecordType::A->value => [
                    new ARecord('example.com', Duration::seconds(300), Address::parse('1.2.3.4')),
                ],
            ],
            'socks.local' => [
                RecordType::A->value => [
                    new ARecord('socks.local', Duration::seconds(300), Address::parse('10.0.0.50')),
                ],
            ],
        ]);

        $connector = new DNS\HTTP\Connector($inner, $resolver);
        $config = new ClientConfiguration(socksConfiguration: new \Psl\Socks\Configuration('socks.local', 1080));

        $connector->connect(
            new Origin('http', 'example.com', 80),
            new Request(method: 'GET', url: parse('http://example.com/')),
            $config,
        );

        static::assertNotNull($receivedConfig);
        static::assertNotNull($receivedConfig->socksConfiguration);
        static::assertSame('10.0.0.50', $receivedConfig->socksConfiguration->proxyHost);
        static::assertSame(1080, $receivedConfig->socksConfiguration->proxyPort);
    }

    public function testDoesNotResolveSocksProxyWithIpAddress(): void
    {
        $receivedConfig = null;

        $inner = $this->createMockConnector(static function (Origin $o, Request $r, ClientConfiguration $c) use (
            &$receivedConfig,
        ): void {
            $receivedConfig = $c;
        });

        $resolver = new DNS\StaticResolver([
            'example.com' => [
                RecordType::A->value => [
                    new ARecord('example.com', Duration::seconds(300), Address::parse('1.2.3.4')),
                ],
            ],
        ]);

        $connector = new DNS\HTTP\Connector($inner, $resolver);
        $config = new ClientConfiguration(socksConfiguration: new \Psl\Socks\Configuration('192.168.1.1', 1080));

        $connector->connect(
            new Origin('http', 'example.com', 80),
            new Request(method: 'GET', url: parse('http://example.com/')),
            $config,
        );

        static::assertNotNull($receivedConfig);
        static::assertNotNull($receivedConfig->socksConfiguration);
        static::assertSame('192.168.1.1', $receivedConfig->socksConfiguration->proxyHost);
    }

    /**
     * @param (Closure(Origin, Request?, ClientConfiguration?): void) $onConnect
     */
    private function createMockConnector(Closure $onConnect): ConnectorInterface
    {
        return new class($onConnect) implements ConnectorInterface {
            public ConnectionMetadata $metadata {
                get => new ConnectionMetadata(Network\Address::tcp(), Network\Address::tcp());
            }

            public function __construct(
                private readonly Closure $onConnect,
            ) {}

            public function connect(
                Origin $origin,
                Request $request,
                ClientConfiguration $configuration,
                Async\CancellationTokenInterface $cancellation = new Async\NullCancellationToken(),
            ): ConnectionInterface {
                ($this->onConnect)($origin, $request, $configuration);

                return new class() implements ConnectionInterface {
                    public ConnectionMetadata $metadata {
                        get => new ConnectionMetadata(Network\Address::tcp(), Network\Address::tcp());
                    }

                    public function exchange(
                        Request $request,
                        ClientConfiguration $configuration,
                        Async\CancellationTokenInterface $cancellation = new Async\NullCancellationToken(),
                    ): Transaction {
                        return new Transaction([], null, new Response(status: 200, headers: FieldMap::from([])));
                    }

                    public function finalize(Transaction $transaction): Transaction
                    {
                        return $transaction;
                    }
                };
            }
        };
    }
}
