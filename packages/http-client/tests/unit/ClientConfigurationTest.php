<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\H2ClientConfiguration;
use Psl\HTTP\Message\ProtocolVersion;
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
        $new = $config->withH2($h2);

        static::assertSame($h2, $new->h2);
        static::assertSame(100, $config->h2->maxConcurrentStreams);
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
        $new = $config->withProxy($proxy);

        static::assertSame($proxy, $new->proxy);
        static::assertNull($config->proxy);
    }

    public function testWithProxyNull(): void
    {
        $proxy = new Socks\Configuration('127.0.0.1', 1080);
        $config = new ClientConfiguration(proxy: $proxy);
        $new = $config->withProxy(null);

        static::assertSame($proxy, $config->proxy);
        static::assertNull($new->proxy);
    }

    public function testWithTunnel(): void
    {
        $config = new ClientConfiguration();
        $new = $config->withTunnel('http://proxy:8080');

        static::assertSame('http://proxy:8080', $new->tunnel);
        static::assertNull($config->tunnel);
    }

    public function testWithTunnelNull(): void
    {
        $config = new ClientConfiguration(tunnel: 'http://proxy:8080');
        $new = $config->withTunnel(null);

        static::assertSame('http://proxy:8080', $config->tunnel);
        static::assertNull($new->tunnel);
    }

    public function testWithNoTunneling(): void
    {
        $config = new ClientConfiguration();
        $new = $config->withNoTunneling(['localhost', '*.internal']);

        static::assertSame(['localhost', '*.internal'], $new->noTunneling);
        static::assertSame([], $config->noTunneling);
    }

    public function testWithProxyPreservesOtherFields(): void
    {
        $config = new ClientConfiguration(tunnel: 'http://proxy:8080', noTunneling: ['localhost']);
        $proxy = new Socks\Configuration('127.0.0.1', 1080);
        $new = $config->withProxy($proxy);

        static::assertSame($proxy, $new->proxy);
        static::assertSame('http://proxy:8080', $new->tunnel);
        static::assertSame(['localhost'], $new->noTunneling);
    }

    public function testWithTunnelPreservesOtherFields(): void
    {
        $proxy = new Socks\Configuration('127.0.0.1', 1080);
        $config = new ClientConfiguration(proxy: $proxy, noTunneling: ['localhost']);
        $new = $config->withTunnel('http://other:9090');

        static::assertSame('http://other:9090', $new->tunnel);
        static::assertSame($proxy, $new->proxy);
        static::assertSame(['localhost'], $new->noTunneling);
    }

    public function testWithChaining(): void
    {
        $config = new ClientConfiguration()
            ->withMaxResponseBodySize(1_000_000)
            ->withProtocolVersions([ProtocolVersion::V11])
            ->withTunnel('http://proxy:8080')
            ->withNoTunneling(['localhost']);

        static::assertSame(1_000_000, $config->maxResponseBodySize);
        static::assertSame([ProtocolVersion::V11], $config->protocolVersions);
        static::assertSame('http://proxy:8080', $config->tunnel);
        static::assertSame(['localhost'], $config->noTunneling);
    }
}
