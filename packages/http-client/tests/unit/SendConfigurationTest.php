<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\H2ClientConfiguration;
use Psl\HTTP\Client\SendConfiguration;
use Psl\HTTP\Message\ProtocolVersion;
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
        static::assertNull($config->tunnel);
        static::assertNull($config->noTunneling);
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

    public function testWithTunnel(): void
    {
        $config = new SendConfiguration();
        $new = $config->withTunnel('http://proxy:8080');

        static::assertSame('http://proxy:8080', $new->tunnel);
        static::assertNull($config->tunnel);
    }

    public function testWithNoTunneling(): void
    {
        $config = new SendConfiguration();
        $new = $config->withNoTunneling(['localhost', '.example.com']);

        static::assertSame(['localhost', '.example.com'], $new->noTunneling);
        static::assertNull($config->noTunneling);
    }

    public function testOverridesAppliedToClientConfiguration(): void
    {
        $clientConfig = new ClientConfiguration(
            maxResponseHeaderSize: 4_096,
            maxResponseBodySize: 1_048_576,
            protocolVersions: [ProtocolVersion::V20, ProtocolVersion::V11],
            tunnel: 'http://proxy:8080',
            noTunneling: ['localhost'],
        );

        $sendConfig = new SendConfiguration(maxResponseBodySize: 500_000_000);

        $merged = $clientConfig->withOverrides($sendConfig);

        static::assertSame(500_000_000, $merged->maxResponseBodySize);
        static::assertSame(4_096, $merged->maxResponseHeaderSize);
        static::assertSame([ProtocolVersion::V20, ProtocolVersion::V11], $merged->protocolVersions);
        static::assertSame('http://proxy:8080', $merged->tunnel);
        static::assertSame(['localhost'], $merged->noTunneling);
    }

    public function testNullOverridesPreserveClientDefaults(): void
    {
        $tls = new TLS\ClientConfiguration(peerVerification: false);
        $url = URL\parse('https://api.example.com');
        $clientConfig = new ClientConfiguration(
            maxResponseHeaderSize: 4_096,
            maxResponseBodySize: 2_097_152,
            baseUrl: $url,
            tlsConfiguration: $tls,
            protocolVersions: [ProtocolVersion::V11],
            tunnel: 'http://proxy:3128',
            noTunneling: ['localhost'],
        );

        $sendConfig = new SendConfiguration();

        $merged = $clientConfig->withOverrides($sendConfig);

        static::assertSame(4_096, $merged->maxResponseHeaderSize);
        static::assertSame(2_097_152, $merged->maxResponseBodySize);
        static::assertSame($url, $merged->baseUrl);
        static::assertSame($tls, $merged->tlsConfiguration);
        static::assertSame([ProtocolVersion::V11], $merged->protocolVersions);
        static::assertSame('http://proxy:3128', $merged->tunnel);
        static::assertSame(['localhost'], $merged->noTunneling);
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
        $clientConfig = new ClientConfiguration(h2: $h2, unixSocket: '/var/run/docker.sock', proxy: $proxy);

        $sendConfig = new SendConfiguration(maxResponseBodySize: 999_999, tunnel: 'http://tunnel:8080');

        $merged = $clientConfig->withOverrides($sendConfig);

        static::assertSame($proxy, $merged->proxy);
        static::assertSame('/var/run/docker.sock', $merged->unixSocket);
        static::assertSame($h2, $merged->h2);
        static::assertSame(999_999, $merged->maxResponseBodySize);
        static::assertSame('http://tunnel:8080', $merged->tunnel);
    }
}
