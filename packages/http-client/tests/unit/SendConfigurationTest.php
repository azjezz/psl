<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\DateTime\Duration;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Connection\ConnectionMetadata;
use Psl\HTTP\Client\H2ClientConfiguration;
use Psl\HTTP\Client\ProxyConfiguration;
use Psl\HTTP\Client\SendConfiguration;
use Psl\HTTP\Message\ProtocolVersion;
use Psl\HTTP\Message\Response;
use Psl\Socks;
use Psl\TLS;
use Psl\URL;

final class SendConfigurationTest extends TestCase
{
    public function testDefaultsAreAllNull(): void
    {
        $config = new SendConfiguration();

        static::assertNull($config->maxResponseHeaderSize);
        static::assertNull($config->maxResponseBodySize);
        static::assertNull($config->baseUrl);
        static::assertNull($config->tlsConfiguration);
        static::assertNull($config->protocolVersions);
        static::assertNull($config->proxyConfiguration);
        static::assertNull($config->onInformationalResponse);
        static::assertNull($config->onConnection);
        static::assertNull($config->connectionTimeout);
    }

    public function testWithMaxResponseHeaderSize(): void
    {
        $config = new SendConfiguration();
        $new = $config->withMaxResponseHeaderSize(16_384);

        static::assertSame(16_384, $new->maxResponseHeaderSize);
        static::assertNull($config->maxResponseHeaderSize);
    }

    public function testWithMaxResponseBodySize(): void
    {
        $config = new SendConfiguration();
        $new = $config->withMaxResponseBodySize(500_000_000);

        static::assertSame(500_000_000, $new->maxResponseBodySize);
        static::assertNull($config->maxResponseBodySize);
    }

    public function testWithBaseUrl(): void
    {
        $config = new SendConfiguration();
        $url = URL\parse('https://example.com');
        $new = $config->withBaseUrl($url);

        static::assertSame($url, $new->baseUrl);
        static::assertNull($config->baseUrl);
    }

    public function testWithTlsConfiguration(): void
    {
        $config = new SendConfiguration();
        $tls = new TLS\ClientConfiguration(peerVerification: false);
        $new = $config->withTlsConfiguration($tls);

        static::assertSame($tls, $new->tlsConfiguration);
        static::assertNull($config->tlsConfiguration);
    }

    public function testWithProtocolVersions(): void
    {
        $config = new SendConfiguration();
        $new = $config->withProtocolVersions([ProtocolVersion::V11]);

        static::assertSame([ProtocolVersion::V11], $new->protocolVersions);
        static::assertNull($config->protocolVersions);
    }

    public function testWithProxy(): void
    {
        $config = new SendConfiguration();
        $proxy = new ProxyConfiguration(URL\parse('http://proxy:8080'));
        $new = $config->withProxyConfiguration($proxy);

        static::assertSame($proxy, $new->proxyConfiguration);
        static::assertNull($config->proxyConfiguration);
    }

    public function testWithOnInformationalResponse(): void
    {
        $config = new SendConfiguration();
        $cb = static function (Response $r): void {};
        $new = $config->withOnInformationalResponse($cb);

        static::assertNotSame($config, $new);
        static::assertSame($cb, $new->onInformationalResponse);
        static::assertNull($config->onInformationalResponse);
    }

    public function testWithOnInformationalResponseNull(): void
    {
        $cb = static function (Response $r): void {};
        $config = new SendConfiguration(onInformationalResponse: $cb);
        $new = $config->withOnInformationalResponse(null);

        static::assertNull($new->onInformationalResponse);
        static::assertSame($cb, $config->onInformationalResponse);
    }

    public function testWithOnConnection(): void
    {
        $config = new SendConfiguration();
        $cb = static function (ConnectionMetadata $m): void {};
        $new = $config->withOnConnection($cb);

        static::assertNotSame($config, $new);
        static::assertSame($cb, $new->onConnection);
        static::assertNull($config->onConnection);
    }

    public function testWithOnConnectionNull(): void
    {
        $cb = static function (ConnectionMetadata $m): void {};
        $config = new SendConfiguration(onConnection: $cb);
        $new = $config->withOnConnection(null);

        static::assertNull($new->onConnection);
        static::assertSame($cb, $config->onConnection);
    }

    public function testWithConnectionTimeout(): void
    {
        $config = new SendConfiguration();
        $timeout = Duration::seconds(30);
        $new = $config->withConnectionTimeout($timeout);

        static::assertNotSame($config, $new);
        static::assertSame($timeout, $new->connectionTimeout);
        static::assertNull($config->connectionTimeout);
    }

    public function testWithConnectionTimeoutNull(): void
    {
        $timeout = Duration::seconds(30);
        $config = new SendConfiguration(connectionTimeout: $timeout);
        $new = $config->withConnectionTimeout(null);

        static::assertNull($new->connectionTimeout);
        static::assertSame($timeout, $config->connectionTimeout);
    }

    public function testWithMaxResponseHeaderSizePreservesOtherFields(): void
    {
        $url = URL\parse('https://example.com');
        $tls = new TLS\ClientConfiguration(peerVerification: false);
        $proxy = new ProxyConfiguration(URL\parse('http://proxy:8080'));
        $onInfo = static function (Response $r): void {};
        $onConn = static function (ConnectionMetadata $m): void {};
        $timeout = Duration::seconds(10);

        $config = new SendConfiguration(
            maxResponseHeaderSize: 4_096,
            maxResponseBodySize: 1_000_000,
            baseUrl: $url,
            tlsConfiguration: $tls,
            protocolVersions: [ProtocolVersion::V11],
            proxyConfiguration: $proxy,
            onInformationalResponse: $onInfo,
            onConnection: $onConn,
            connectionTimeout: $timeout,
        );

        $new = $config->withMaxResponseHeaderSize(16_384);

        static::assertSame(16_384, $new->maxResponseHeaderSize);
        static::assertSame(1_000_000, $new->maxResponseBodySize);
        static::assertSame($url, $new->baseUrl);
        static::assertSame($tls, $new->tlsConfiguration);
        static::assertSame([ProtocolVersion::V11], $new->protocolVersions);
        static::assertSame($proxy, $new->proxyConfiguration);
        static::assertSame($onInfo, $new->onInformationalResponse);
        static::assertSame($onConn, $new->onConnection);
        static::assertSame($timeout, $new->connectionTimeout);
    }

    public function testWithMaxResponseBodySizePreservesOtherFields(): void
    {
        $url = URL\parse('https://example.com');
        $tls = new TLS\ClientConfiguration(peerVerification: false);
        $proxy = new ProxyConfiguration(URL\parse('http://proxy:8080'));
        $onInfo = static function (Response $r): void {};
        $onConn = static function (ConnectionMetadata $m): void {};
        $timeout = Duration::seconds(10);

        $config = new SendConfiguration(
            maxResponseHeaderSize: 4_096,
            maxResponseBodySize: 1_000_000,
            baseUrl: $url,
            tlsConfiguration: $tls,
            protocolVersions: [ProtocolVersion::V11],
            proxyConfiguration: $proxy,
            onInformationalResponse: $onInfo,
            onConnection: $onConn,
            connectionTimeout: $timeout,
        );

        $new = $config->withMaxResponseBodySize(500_000_000);

        static::assertSame(4_096, $new->maxResponseHeaderSize);
        static::assertSame(500_000_000, $new->maxResponseBodySize);
        static::assertSame($url, $new->baseUrl);
        static::assertSame($tls, $new->tlsConfiguration);
        static::assertSame([ProtocolVersion::V11], $new->protocolVersions);
        static::assertSame($proxy, $new->proxyConfiguration);
        static::assertSame($onInfo, $new->onInformationalResponse);
        static::assertSame($onConn, $new->onConnection);
        static::assertSame($timeout, $new->connectionTimeout);
    }

    public function testWithBaseUrlPreservesOtherFields(): void
    {
        $tls = new TLS\ClientConfiguration(peerVerification: false);
        $proxy = new ProxyConfiguration(URL\parse('http://proxy:8080'));
        $onInfo = static function (Response $r): void {};
        $onConn = static function (ConnectionMetadata $m): void {};
        $timeout = Duration::seconds(10);

        $config = new SendConfiguration(
            maxResponseHeaderSize: 4_096,
            maxResponseBodySize: 1_000_000,
            baseUrl: URL\parse('https://old.example.com'),
            tlsConfiguration: $tls,
            protocolVersions: [ProtocolVersion::V11],
            proxyConfiguration: $proxy,
            onInformationalResponse: $onInfo,
            onConnection: $onConn,
            connectionTimeout: $timeout,
        );

        $newUrl = URL\parse('https://new.example.com');
        $new = $config->withBaseUrl($newUrl);

        static::assertSame(4_096, $new->maxResponseHeaderSize);
        static::assertSame(1_000_000, $new->maxResponseBodySize);
        static::assertSame($newUrl, $new->baseUrl);
        static::assertSame($tls, $new->tlsConfiguration);
        static::assertSame([ProtocolVersion::V11], $new->protocolVersions);
        static::assertSame($proxy, $new->proxyConfiguration);
        static::assertSame($onInfo, $new->onInformationalResponse);
        static::assertSame($onConn, $new->onConnection);
        static::assertSame($timeout, $new->connectionTimeout);
    }

    public function testWithTlsConfigurationPreservesOtherFields(): void
    {
        $url = URL\parse('https://example.com');
        $proxy = new ProxyConfiguration(URL\parse('http://proxy:8080'));
        $onInfo = static function (Response $r): void {};
        $onConn = static function (ConnectionMetadata $m): void {};
        $timeout = Duration::seconds(10);

        $config = new SendConfiguration(
            maxResponseHeaderSize: 4_096,
            maxResponseBodySize: 1_000_000,
            baseUrl: $url,
            tlsConfiguration: new TLS\ClientConfiguration(peerVerification: true),
            protocolVersions: [ProtocolVersion::V11],
            proxyConfiguration: $proxy,
            onInformationalResponse: $onInfo,
            onConnection: $onConn,
            connectionTimeout: $timeout,
        );

        $newTls = new TLS\ClientConfiguration(peerVerification: false);
        $new = $config->withTlsConfiguration($newTls);

        static::assertSame(4_096, $new->maxResponseHeaderSize);
        static::assertSame(1_000_000, $new->maxResponseBodySize);
        static::assertSame($url, $new->baseUrl);
        static::assertSame($newTls, $new->tlsConfiguration);
        static::assertSame([ProtocolVersion::V11], $new->protocolVersions);
        static::assertSame($proxy, $new->proxyConfiguration);
        static::assertSame($onInfo, $new->onInformationalResponse);
        static::assertSame($onConn, $new->onConnection);
        static::assertSame($timeout, $new->connectionTimeout);
    }

    public function testWithProtocolVersionsPreservesOtherFields(): void
    {
        $url = URL\parse('https://example.com');
        $tls = new TLS\ClientConfiguration(peerVerification: false);
        $proxy = new ProxyConfiguration(URL\parse('http://proxy:8080'));
        $onInfo = static function (Response $r): void {};
        $onConn = static function (ConnectionMetadata $m): void {};
        $timeout = Duration::seconds(10);

        $config = new SendConfiguration(
            maxResponseHeaderSize: 4_096,
            maxResponseBodySize: 1_000_000,
            baseUrl: $url,
            tlsConfiguration: $tls,
            protocolVersions: [ProtocolVersion::V20, ProtocolVersion::V11],
            proxyConfiguration: $proxy,
            onInformationalResponse: $onInfo,
            onConnection: $onConn,
            connectionTimeout: $timeout,
        );

        $new = $config->withProtocolVersions([ProtocolVersion::V11]);

        static::assertSame(4_096, $new->maxResponseHeaderSize);
        static::assertSame(1_000_000, $new->maxResponseBodySize);
        static::assertSame($url, $new->baseUrl);
        static::assertSame($tls, $new->tlsConfiguration);
        static::assertSame([ProtocolVersion::V11], $new->protocolVersions);
        static::assertSame($proxy, $new->proxyConfiguration);
        static::assertSame($onInfo, $new->onInformationalResponse);
        static::assertSame($onConn, $new->onConnection);
        static::assertSame($timeout, $new->connectionTimeout);
    }

    public function testWithProxyConfigurationPreservesOtherFields(): void
    {
        $url = URL\parse('https://example.com');
        $tls = new TLS\ClientConfiguration(peerVerification: false);
        $onInfo = static function (Response $r): void {};
        $onConn = static function (ConnectionMetadata $m): void {};
        $timeout = Duration::seconds(10);

        $config = new SendConfiguration(
            maxResponseHeaderSize: 4_096,
            maxResponseBodySize: 1_000_000,
            baseUrl: $url,
            tlsConfiguration: $tls,
            protocolVersions: [ProtocolVersion::V11],
            proxyConfiguration: new ProxyConfiguration(URL\parse('http://old-proxy:8080')),
            onInformationalResponse: $onInfo,
            onConnection: $onConn,
            connectionTimeout: $timeout,
        );

        $newProxy = new ProxyConfiguration(URL\parse('http://new-proxy:3128'));
        $new = $config->withProxyConfiguration($newProxy);

        static::assertSame(4_096, $new->maxResponseHeaderSize);
        static::assertSame(1_000_000, $new->maxResponseBodySize);
        static::assertSame($url, $new->baseUrl);
        static::assertSame($tls, $new->tlsConfiguration);
        static::assertSame([ProtocolVersion::V11], $new->protocolVersions);
        static::assertSame($newProxy, $new->proxyConfiguration);
        static::assertSame($onInfo, $new->onInformationalResponse);
        static::assertSame($onConn, $new->onConnection);
        static::assertSame($timeout, $new->connectionTimeout);
    }

    public function testWithOnInformationalResponsePreservesOtherFields(): void
    {
        $url = URL\parse('https://example.com');
        $tls = new TLS\ClientConfiguration(peerVerification: false);
        $proxy = new ProxyConfiguration(URL\parse('http://proxy:8080'));
        $onConn = static function (ConnectionMetadata $m): void {};
        $timeout = Duration::seconds(10);

        $config = new SendConfiguration(
            maxResponseHeaderSize: 4_096,
            maxResponseBodySize: 1_000_000,
            baseUrl: $url,
            tlsConfiguration: $tls,
            protocolVersions: [ProtocolVersion::V11],
            proxyConfiguration: $proxy,
            onInformationalResponse: static function (Response $r): void {},
            onConnection: $onConn,
            connectionTimeout: $timeout,
        );

        $newCb = static function (Response $r): void {};
        $new = $config->withOnInformationalResponse($newCb);

        static::assertSame(4_096, $new->maxResponseHeaderSize);
        static::assertSame(1_000_000, $new->maxResponseBodySize);
        static::assertSame($url, $new->baseUrl);
        static::assertSame($tls, $new->tlsConfiguration);
        static::assertSame([ProtocolVersion::V11], $new->protocolVersions);
        static::assertSame($proxy, $new->proxyConfiguration);
        static::assertSame($newCb, $new->onInformationalResponse);
        static::assertSame($onConn, $new->onConnection);
        static::assertSame($timeout, $new->connectionTimeout);
    }

    public function testWithOnConnectionPreservesOtherFields(): void
    {
        $url = URL\parse('https://example.com');
        $tls = new TLS\ClientConfiguration(peerVerification: false);
        $proxy = new ProxyConfiguration(URL\parse('http://proxy:8080'));
        $onInfo = static function (Response $r): void {};
        $timeout = Duration::seconds(10);

        $config = new SendConfiguration(
            maxResponseHeaderSize: 4_096,
            maxResponseBodySize: 1_000_000,
            baseUrl: $url,
            tlsConfiguration: $tls,
            protocolVersions: [ProtocolVersion::V11],
            proxyConfiguration: $proxy,
            onInformationalResponse: $onInfo,
            onConnection: static function (ConnectionMetadata $m): void {},
            connectionTimeout: $timeout,
        );

        $newCb = static function (ConnectionMetadata $m): void {};
        $new = $config->withOnConnection($newCb);

        static::assertSame(4_096, $new->maxResponseHeaderSize);
        static::assertSame(1_000_000, $new->maxResponseBodySize);
        static::assertSame($url, $new->baseUrl);
        static::assertSame($tls, $new->tlsConfiguration);
        static::assertSame([ProtocolVersion::V11], $new->protocolVersions);
        static::assertSame($proxy, $new->proxyConfiguration);
        static::assertSame($onInfo, $new->onInformationalResponse);
        static::assertSame($newCb, $new->onConnection);
        static::assertSame($timeout, $new->connectionTimeout);
    }

    public function testWithConnectionTimeoutPreservesOtherFields(): void
    {
        $url = URL\parse('https://example.com');
        $tls = new TLS\ClientConfiguration(peerVerification: false);
        $proxy = new ProxyConfiguration(URL\parse('http://proxy:8080'));
        $onInfo = static function (Response $r): void {};
        $onConn = static function (ConnectionMetadata $m): void {};

        $config = new SendConfiguration(
            maxResponseHeaderSize: 4_096,
            maxResponseBodySize: 1_000_000,
            baseUrl: $url,
            tlsConfiguration: $tls,
            protocolVersions: [ProtocolVersion::V11],
            proxyConfiguration: $proxy,
            onInformationalResponse: $onInfo,
            onConnection: $onConn,
            connectionTimeout: Duration::seconds(10),
        );

        $newTimeout = Duration::seconds(60);
        $new = $config->withConnectionTimeout($newTimeout);

        static::assertSame(4_096, $new->maxResponseHeaderSize);
        static::assertSame(1_000_000, $new->maxResponseBodySize);
        static::assertSame($url, $new->baseUrl);
        static::assertSame($tls, $new->tlsConfiguration);
        static::assertSame([ProtocolVersion::V11], $new->protocolVersions);
        static::assertSame($proxy, $new->proxyConfiguration);
        static::assertSame($onInfo, $new->onInformationalResponse);
        static::assertSame($onConn, $new->onConnection);
        static::assertSame($newTimeout, $new->connectionTimeout);
    }

    public function testWithChaining(): void
    {
        $url = URL\parse('https://example.com');
        $proxy = new ProxyConfiguration(URL\parse('http://proxy:8080'));
        $cb = static function (Response $r): void {};
        $timeout = Duration::seconds(15);

        $config = new SendConfiguration()
            ->withMaxResponseHeaderSize(16_384)
            ->withMaxResponseBodySize(500_000_000)
            ->withBaseUrl($url)
            ->withProtocolVersions([ProtocolVersion::V11])
            ->withProxyConfiguration($proxy)
            ->withOnInformationalResponse($cb)
            ->withConnectionTimeout($timeout);

        static::assertSame(16_384, $config->maxResponseHeaderSize);
        static::assertSame(500_000_000, $config->maxResponseBodySize);
        static::assertSame($url, $config->baseUrl);
        static::assertSame([ProtocolVersion::V11], $config->protocolVersions);
        static::assertSame($proxy, $config->proxyConfiguration);
        static::assertSame($cb, $config->onInformationalResponse);
        static::assertSame($timeout, $config->connectionTimeout);
    }

    public function testOverridesAppliedToClientConfiguration(): void
    {
        $proxy = new ProxyConfiguration(URL\parse('http://proxy:8080'), skipProxyFor: ['localhost']);
        $clientConfig = new ClientConfiguration(
            maxResponseHeaderSize: 4_096,
            maxResponseBodySize: 1_048_576,
            protocolVersions: [ProtocolVersion::V20, ProtocolVersion::V11],
            proxyConfiguration: $proxy,
        );

        $sendConfig = new SendConfiguration(maxResponseBodySize: 500_000_000);

        $merged = $clientConfig->withOverrides($sendConfig);

        static::assertSame(500_000_000, $merged->maxResponseBodySize);
        static::assertSame(4_096, $merged->maxResponseHeaderSize);
        static::assertSame([ProtocolVersion::V20, ProtocolVersion::V11], $merged->protocolVersions);
        static::assertSame($proxy, $merged->proxyConfiguration);
    }

    public function testNullOverridesPreserveClientDefaults(): void
    {
        $tls = new TLS\ClientConfiguration(peerVerification: false);
        $url = URL\parse('https://api.example.com');
        $proxy = new ProxyConfiguration(URL\parse('http://proxy:3128'), skipProxyFor: ['localhost']);
        $clientConfig = new ClientConfiguration(
            maxResponseHeaderSize: 4_096,
            maxResponseBodySize: 2_097_152,
            baseUrl: $url,
            tlsConfiguration: $tls,
            protocolVersions: [ProtocolVersion::V11],
            proxyConfiguration: $proxy,
        );

        $sendConfig = new SendConfiguration();

        $merged = $clientConfig->withOverrides($sendConfig);

        static::assertSame(4_096, $merged->maxResponseHeaderSize);
        static::assertSame(2_097_152, $merged->maxResponseBodySize);
        static::assertSame($url, $merged->baseUrl);
        static::assertSame($tls, $merged->tlsConfiguration);
        static::assertSame([ProtocolVersion::V11], $merged->protocolVersions);
        static::assertSame($proxy, $merged->proxyConfiguration);
    }

    public function testMultipleOverridesApplied(): void
    {
        $clientConfig = new ClientConfiguration(
            maxResponseHeaderSize: 8_192,
            maxResponseBodySize: 10_485_760,
            protocolVersions: [ProtocolVersion::V20, ProtocolVersion::V11],
        );

        $sendConfig = new SendConfiguration(maxResponseHeaderSize: 16_384, protocolVersions: [ProtocolVersion::V11]);

        $merged = $clientConfig->withOverrides($sendConfig);

        static::assertSame(16_384, $merged->maxResponseHeaderSize);
        static::assertSame([ProtocolVersion::V11], $merged->protocolVersions);
        static::assertSame(10_485_760, $merged->maxResponseBodySize);
    }

    public function testProxyNotOverridable(): void
    {
        $proxy = new Socks\Configuration(proxyHost: 'socks.example.com', proxyPort: 1080);
        $h2 = new H2ClientConfiguration(maxConcurrentStreams: 50);
        $clientConfig = new ClientConfiguration(
            h2ClientConfiguration: $h2,
            unixSocket: '/var/run/docker.sock',
            socksConfiguration: $proxy,
        );

        $httpProxy = new ProxyConfiguration(URL\parse('http://tunnel:8080'));
        $sendConfig = new SendConfiguration(maxResponseBodySize: 999_999, proxyConfiguration: $httpProxy);

        $merged = $clientConfig->withOverrides($sendConfig);

        static::assertSame($proxy, $merged->socksConfiguration);
        static::assertSame('/var/run/docker.sock', $merged->unixSocket);
        static::assertSame($h2, $merged->h2ClientConfiguration);
        static::assertSame(999_999, $merged->maxResponseBodySize);
        static::assertSame($httpProxy, $merged->proxyConfiguration);
    }
}
