<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\H2ClientConfiguration;
use Psl\HTTP\Client\ProxyConfiguration;
use Psl\HTTP\Client\SendConfiguration;
use Psl\HTTP\Message\ProtocolVersion;
use Psl\HTTP\Message\Response;
use Psl\Socks;
use Psl\TLS;
use Psl\URL;

final class ClientConfigurationTest extends TestCase
{
    public function testWithMaxResponseHeaderSize(): void
    {
        $config = new ClientConfiguration();
        $new = $config->withMaxResponseHeaderSize(16_384);

        static::assertSame(16_384, $new->maxResponseHeaderSize);
        static::assertSame(8_192, $config->maxResponseHeaderSize);
    }

    public function testWithMaxResponseBodySize(): void
    {
        $config = new ClientConfiguration();
        $new = $config->withMaxResponseBodySize(1_048_576);

        static::assertSame(1_048_576, $new->maxResponseBodySize);
        static::assertSame(10_485_760, $config->maxResponseBodySize);
    }

    public function testWithBaseUrl(): void
    {
        $config = new ClientConfiguration();
        $url = URL\parse('https://example.com');
        $new = $config->withBaseUrl($url);

        static::assertSame($url, $new->baseUrl);
        static::assertNull($config->baseUrl);
    }

    public function testWithTlsConfiguration(): void
    {
        $config = new ClientConfiguration();
        $tls = new TLS\ClientConfiguration(peerVerification: false);
        $new = $config->withTlsConfiguration($tls);

        static::assertSame($tls, $new->tlsConfiguration);
        static::assertNotSame($tls, $config->tlsConfiguration);
    }

    public function testWithH2(): void
    {
        $config = new ClientConfiguration();
        $h2 = new H2ClientConfiguration(maxConcurrentStreams: 50);
        $new = $config->withH2ClientConfiguration($h2);

        static::assertSame($h2, $new->h2ClientConfiguration);
        static::assertSame(100, $config->h2ClientConfiguration->maxConcurrentStreams);
    }

    public function testWithProtocolVersions(): void
    {
        $config = new ClientConfiguration();
        $new = $config->withProtocolVersions([ProtocolVersion::V11]);

        static::assertSame([ProtocolVersion::V11], $new->protocolVersions);
        static::assertCount(2, $config->protocolVersions);
    }

    public function testWithUnixSocket(): void
    {
        $config = new ClientConfiguration();
        $new = $config->withUnixSocket('/tmp/test.sock');

        static::assertSame('/tmp/test.sock', $new->unixSocket);
        static::assertNull($config->unixSocket);
    }

    public function testWithProxy(): void
    {
        $config = new ClientConfiguration();
        $proxy = new Socks\Configuration('127.0.0.1', 1080);
        $new = $config->withSocksConfiguration($proxy);

        static::assertSame($proxy, $new->socksConfiguration);
        static::assertNull($config->socksConfiguration);
    }

    public function testWithProxyNull(): void
    {
        $proxy = new Socks\Configuration('127.0.0.1', 1080);
        $config = new ClientConfiguration(socksConfiguration: $proxy);
        $new = $config->withSocksConfiguration(null);

        static::assertSame($proxy, $config->socksConfiguration);
        static::assertNull($new->socksConfiguration);
    }

    public function testWithHttpProxy(): void
    {
        $config = new ClientConfiguration();
        $proxy = new ProxyConfiguration(URL\parse('http://proxy:8080'));
        $new = $config->withProxyConfiguration($proxy);

        static::assertSame($proxy, $new->proxyConfiguration);
        static::assertNull($config->proxyConfiguration);
    }

    public function testWithHttpProxyNull(): void
    {
        $proxy = new ProxyConfiguration(URL\parse('http://proxy:8080'));
        $config = new ClientConfiguration(proxyConfiguration: $proxy);
        $new = $config->withProxyConfiguration(null);

        static::assertSame($proxy, $config->proxyConfiguration);
        static::assertNull($new->proxyConfiguration);
    }

    public function testWithSocksPreservesProxy(): void
    {
        $httpProxy = new ProxyConfiguration(URL\parse('http://proxy:8080'), skipProxyFor: ['localhost']);
        $config = new ClientConfiguration(proxyConfiguration: $httpProxy);
        $socksProxy = new Socks\Configuration('127.0.0.1', 1080);
        $new = $config->withSocksConfiguration($socksProxy);

        static::assertSame($socksProxy, $new->socksConfiguration);
        static::assertSame($httpProxy, $new->proxyConfiguration);
    }

    public function testWithProxyPreservesSocks(): void
    {
        $socksProxy = new Socks\Configuration('127.0.0.1', 1080);
        $config = new ClientConfiguration(socksConfiguration: $socksProxy);
        $httpProxy = new ProxyConfiguration(URL\parse('http://other:9090'));
        $new = $config->withProxyConfiguration($httpProxy);

        static::assertSame($httpProxy, $new->proxyConfiguration);
        static::assertSame($socksProxy, $new->socksConfiguration);
    }

    public function testWithChaining(): void
    {
        $proxy = new ProxyConfiguration(URL\parse('http://proxy:8080'), skipProxyFor: ['localhost']);
        $config = new ClientConfiguration()
            ->withMaxResponseBodySize(1_000_000)
            ->withProtocolVersions([ProtocolVersion::V11])
            ->withProxyConfiguration($proxy);

        static::assertSame(1_000_000, $config->maxResponseBodySize);
        static::assertSame([ProtocolVersion::V11], $config->protocolVersions);
        static::assertSame($proxy, $config->proxyConfiguration);
    }

    public function testOnInformationalResponseDefault(): void
    {
        $config = new ClientConfiguration();

        static::assertNull($config->onInformationalResponse);
    }

    public function testOnInformationalResponseSet(): void
    {
        $cb = static function (Response $r): void {};
        $config = new ClientConfiguration(onInformationalResponse: $cb);

        static::assertSame($cb, $config->onInformationalResponse);
    }

    public function testWithOverridesMergesNeitherSet(): void
    {
        $config = new ClientConfiguration();
        $merged = $config->withOverrides(new SendConfiguration());

        static::assertNull($merged->onInformationalResponse);
    }

    public function testWithOverridesMergesOnlyClientSet(): void
    {
        $called = false;
        $cb = static function (Response $r) use (&$called): void {
            $called = true;
        };
        $config = new ClientConfiguration(onInformationalResponse: $cb);
        $merged = $config->withOverrides(new SendConfiguration());

        static::assertNotNull($merged->onInformationalResponse);
        ($merged->onInformationalResponse)(new Response(status: 100));
        static::assertTrue($called);
    }

    public function testWithOverridesMergesOnlySendSet(): void
    {
        $called = false;
        $cb = static function (Response $r) use (&$called): void {
            $called = true;
        };
        $config = new ClientConfiguration();
        $merged = $config->withOverrides(new SendConfiguration(onInformationalResponse: $cb));

        static::assertNotNull($merged->onInformationalResponse);
        ($merged->onInformationalResponse)(new Response(status: 100));
        static::assertTrue($called);
    }

    public function testWithOverridesMergesBothSet(): void
    {
        $order = [];
        $clientCb = static function (Response $r) use (&$order): void {
            $order[] = 'client';
        };
        $sendCb = static function (Response $r) use (&$order): void {
            $order[] = 'send';
        };

        $config = new ClientConfiguration(onInformationalResponse: $clientCb);
        $merged = $config->withOverrides(new SendConfiguration(onInformationalResponse: $sendCb));

        static::assertNotNull($merged->onInformationalResponse);
        ($merged->onInformationalResponse)(new Response(status: 100));
        static::assertSame(['client', 'send'], $order);
    }
}
