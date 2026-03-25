<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\H2ClientConfiguration;
use Psl\HTTP\Message\ProtocolVersion;
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

    public function testWithChaining(): void
    {
        $config = new ClientConfiguration()
            ->withMaxResponseBodySize(1_000_000)
            ->withProtocolVersions([ProtocolVersion::V11]);

        static::assertSame(1_000_000, $config->maxResponseBodySize);
        static::assertSame([ProtocolVersion::V11], $config->protocolVersions);
    }
}
