<?php

declare(strict_types=1);

namespace Psl\SMTP\Tests\Unit\Client;

use PHPUnit\Framework\TestCase;
use Psl\SMTP\Client\TransportConfiguration;
use Psl\SMTP\Security;
use Psl\TCP;
use Psl\TLS;

final class TransportConfigurationTest extends TestCase
{
    public function testDefaultConstruction(): void
    {
        $config = new TransportConfiguration();

        static::assertSame('localhost', $config->host);
        static::assertNull($config->port);
        static::assertSame(Security::StartTLS, $config->security);
        static::assertSame('localhost', $config->localHostname);
    }

    public function testStaticDefaultMethod(): void
    {
        $config = TransportConfiguration::default();

        static::assertSame('localhost', $config->host);
        static::assertNull($config->port);
        static::assertSame(Security::StartTLS, $config->security);
        static::assertSame('localhost', $config->localHostname);
    }

    public function testCustomConstruction(): void
    {
        $config = new TransportConfiguration(
            host: 'smtp.example.com',
            port: 587,
            security: Security::StartTLS,
            localHostname: 'client.example.com',
        );

        static::assertSame('smtp.example.com', $config->host);
        static::assertSame(587, $config->port);
        static::assertSame(Security::StartTLS, $config->security);
        static::assertSame('client.example.com', $config->localHostname);
    }

    public function testWithHost(): void
    {
        $original = new TransportConfiguration();
        $modified = $original->withHost('smtp.example.com');

        static::assertSame('smtp.example.com', $modified->host);
        static::assertSame('localhost', $original->host);
        static::assertNotSame($original, $modified);
    }

    public function testWithHostPreservesOtherProperties(): void
    {
        $original = new TransportConfiguration(
            host: 'original.com',
            port: 465,
            security: Security::TLS,
            localHostname: 'local.com',
        );
        $modified = $original->withHost('new.com');

        static::assertSame('new.com', $modified->host);
        static::assertSame(465, $modified->port);
        static::assertSame(Security::TLS, $modified->security);
        static::assertSame('local.com', $modified->localHostname);
    }

    public function testWithPort(): void
    {
        $original = new TransportConfiguration();
        $modified = $original->withPort(465);

        static::assertSame(465, $modified->port);
        static::assertNull($original->port);
        static::assertNotSame($original, $modified);
    }

    public function testWithPortNull(): void
    {
        $original = new TransportConfiguration(port: 587);
        $modified = $original->withPort(null);

        static::assertNull($modified->port);
        static::assertSame(587, $original->port);
    }

    public function testWithPortPreservesOtherProperties(): void
    {
        $original = new TransportConfiguration(
            host: 'smtp.example.com',
            port: 25,
            security: Security::None,
            localHostname: 'myhost',
        );
        $modified = $original->withPort(587);

        static::assertSame('smtp.example.com', $modified->host);
        static::assertSame(587, $modified->port);
        static::assertSame(Security::None, $modified->security);
        static::assertSame('myhost', $modified->localHostname);
    }

    public function testWithSecurity(): void
    {
        $original = new TransportConfiguration();
        $modified = $original->withSecurity(Security::TLS);

        static::assertSame(Security::TLS, $modified->security);
        static::assertSame(Security::StartTLS, $original->security);
        static::assertNotSame($original, $modified);
    }

    public function testWithSecurityNone(): void
    {
        $original = new TransportConfiguration(security: Security::TLS);
        $modified = $original->withSecurity(Security::None);

        static::assertSame(Security::None, $modified->security);
    }

    public function testWithSecurityPreservesOtherProperties(): void
    {
        $original = new TransportConfiguration(
            host: 'test.com',
            port: 25,
            security: Security::None,
            localHostname: 'host',
        );
        $modified = $original->withSecurity(Security::StartTLS);

        static::assertSame('test.com', $modified->host);
        static::assertSame(25, $modified->port);
        static::assertSame(Security::StartTLS, $modified->security);
        static::assertSame('host', $modified->localHostname);
    }

    public function testWithLocalHostname(): void
    {
        $original = new TransportConfiguration();
        $modified = $original->withLocalHostname('myhost.example.com');

        static::assertSame('myhost.example.com', $modified->localHostname);
        static::assertSame('localhost', $original->localHostname);
        static::assertNotSame($original, $modified);
    }

    public function testWithLocalHostnamePreservesOtherProperties(): void
    {
        $original = new TransportConfiguration(
            host: 'smtp.example.com',
            port: 587,
            security: Security::StartTLS,
            localHostname: 'old',
        );
        $modified = $original->withLocalHostname('new');

        static::assertSame('smtp.example.com', $modified->host);
        static::assertSame(587, $modified->port);
        static::assertSame(Security::StartTLS, $modified->security);
        static::assertSame('new', $modified->localHostname);
    }

    public function testWithConnectConfiguration(): void
    {
        $original = new TransportConfiguration();
        $connectConfig = new TCP\ConnectConfiguration();
        $modified = $original->withConnectConfiguration($connectConfig);

        static::assertNotSame($original, $modified);
        static::assertSame($connectConfig, $modified->connectConfiguration);
    }

    public function testWithConnectConfigurationPreservesOtherProperties(): void
    {
        $original = new TransportConfiguration(
            host: 'test.com',
            port: 25,
            security: Security::None,
            localHostname: 'local',
        );
        $connectConfig = new TCP\ConnectConfiguration();
        $modified = $original->withConnectConfiguration($connectConfig);

        static::assertSame('test.com', $modified->host);
        static::assertSame(25, $modified->port);
        static::assertSame(Security::None, $modified->security);
        static::assertSame('local', $modified->localHostname);
    }

    public function testWithTlsClientConfiguration(): void
    {
        $original = new TransportConfiguration();
        $tlsConfig = new TLS\ClientConfiguration();
        $modified = $original->withTlsClientConfiguration($tlsConfig);

        static::assertNotSame($original, $modified);
        static::assertSame($tlsConfig, $modified->tlsClientConfiguration);
    }

    public function testWithTlsClientConfigurationPreservesOtherProperties(): void
    {
        $original = new TransportConfiguration(
            host: 'test.com',
            port: 465,
            security: Security::TLS,
            localHostname: 'local',
        );
        $tlsConfig = new TLS\ClientConfiguration();
        $modified = $original->withTlsClientConfiguration($tlsConfig);

        static::assertSame('test.com', $modified->host);
        static::assertSame(465, $modified->port);
        static::assertSame(Security::TLS, $modified->security);
        static::assertSame('local', $modified->localHostname);
    }

    public function testImmutabilityChain(): void
    {
        $original = new TransportConfiguration();
        $modified = $original
            ->withHost('smtp.example.com')
            ->withPort(465)
            ->withSecurity(Security::TLS)
            ->withLocalHostname('client.example.com');

        static::assertSame('smtp.example.com', $modified->host);
        static::assertSame(465, $modified->port);
        static::assertSame(Security::TLS, $modified->security);
        static::assertSame('client.example.com', $modified->localHostname);

        static::assertSame('localhost', $original->host);
        static::assertNull($original->port);
        static::assertSame(Security::StartTLS, $original->security);
        static::assertSame('localhost', $original->localHostname);
    }

    public function testPortZero(): void
    {
        $config = new TransportConfiguration(port: 0);

        static::assertSame(0, $config->port);
    }

    public function testPortMaxValue(): void
    {
        $config = new TransportConfiguration(port: 65_535);

        static::assertSame(65_535, $config->port);
    }

    public function testAllSecurityModes(): void
    {
        $configNone = new TransportConfiguration(security: Security::None);
        static::assertSame(Security::None, $configNone->security);

        $configStartTLS = new TransportConfiguration(security: Security::StartTLS);
        static::assertSame(Security::StartTLS, $configStartTLS->security);

        $configTLS = new TransportConfiguration(security: Security::TLS);
        static::assertSame(Security::TLS, $configTLS->security);
    }

    public function testWithPipelining(): void
    {
        $config = new TransportConfiguration();
        static::assertTrue($config->pipelining);

        $disabled = $config->withPipelining(false);
        static::assertFalse($disabled->pipelining);
        static::assertTrue($config->pipelining);
    }

    public function testWithPipeliningPreservesOtherProperties(): void
    {
        $config = new TransportConfiguration(host: 'mail.test', chunking: false);

        $modified = $config->withPipelining(false);

        static::assertSame('mail.test', $modified->host);
        static::assertFalse($modified->chunking);
    }

    public function testWithChunking(): void
    {
        $config = new TransportConfiguration();
        static::assertTrue($config->chunking);

        $disabled = $config->withChunking(false);
        static::assertFalse($disabled->chunking);
        static::assertTrue($config->chunking);
    }

    public function testWithChunkingPreservesOtherProperties(): void
    {
        $config = new TransportConfiguration(host: 'mail.test', pipelining: false);

        $modified = $config->withChunking(false);

        static::assertSame('mail.test', $modified->host);
        static::assertFalse($modified->pipelining);
    }

    public function testWithChunkSize(): void
    {
        $config = new TransportConfiguration();
        static::assertSame(65_536, $config->chunkSize);

        $modified = $config->withChunkSize(8192);
        static::assertSame(8192, $modified->chunkSize);
        static::assertSame(65_536, $config->chunkSize);
    }

    public function testWithChunkSizePreservesOtherProperties(): void
    {
        $config = new TransportConfiguration(host: 'mail.test', chunking: false);

        $modified = $config->withChunkSize(1024);

        static::assertSame('mail.test', $modified->host);
        static::assertFalse($modified->chunking);
    }

    public function testWithAllowPartialSuccess(): void
    {
        $config = new TransportConfiguration();
        static::assertFalse($config->allowPartialSuccess);

        $enabled = $config->withAllowPartialSuccess(true);
        static::assertTrue($enabled->allowPartialSuccess);
        static::assertFalse($config->allowPartialSuccess);
    }

    public function testWithAllowPartialSuccessPreservesOtherProperties(): void
    {
        $config = new TransportConfiguration(host: 'mail.test', pipelining: false, chunking: false);

        $modified = $config->withAllowPartialSuccess(true);

        static::assertSame('mail.test', $modified->host);
        static::assertFalse($modified->pipelining);
        static::assertFalse($modified->chunking);
    }

    public function testChainedNewWithers(): void
    {
        $config = TransportConfiguration::default()
            ->withPipelining(false)
            ->withChunking(true)
            ->withChunkSize(32_768)
            ->withAllowPartialSuccess(true);

        static::assertFalse($config->pipelining);
        static::assertTrue($config->chunking);
        static::assertSame(32_768, $config->chunkSize);
        static::assertTrue($config->allowPartialSuccess);
    }

    public function testFullChain(): void
    {
        $config = TransportConfiguration::default()
            ->withHost('smtp.example.com')
            ->withPort(465)
            ->withSecurity(Security::TLS)
            ->withLocalHostname('client.local')
            ->withPipelining(true)
            ->withChunking(false)
            ->withChunkSize(16_384)
            ->withAllowPartialSuccess(true);

        static::assertSame('smtp.example.com', $config->host);
        static::assertSame(465, $config->port);
        static::assertSame(Security::TLS, $config->security);
        static::assertSame('client.local', $config->localHostname);
        static::assertTrue($config->pipelining);
        static::assertFalse($config->chunking);
        static::assertSame(16_384, $config->chunkSize);
        static::assertTrue($config->allowPartialSuccess);
    }
}
