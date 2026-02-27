<?php

declare(strict_types=1);

namespace Psl\Tests\Unit\TLS;

use PHPUnit\Framework\TestCase;
use Psl\TLS\Certificate;
use Psl\TLS\ClientConfig;
use Psl\TLS\Version;

final class ConnectOptionsTest extends TestCase
{
    public function testDefault(): void
    {
        $config = ClientConfig::default();

        static::assertNull($config->peerName);
        static::assertTrue($config->peerVerification);
        static::assertFalse($config->allowSelfSigned);
        static::assertNull($config->certificateAuthority);
        static::assertNull($config->certificateAuthorityPath);
        static::assertNull($config->certificate);
        static::assertNull($config->minimumVersion);
        static::assertNull($config->maximumVersion);
        static::assertNull($config->ciphers);
        static::assertSame(2, $config->securityLevel);
        static::assertNull($config->alpnProtocols);
        static::assertTrue($config->sessionTickets);
    }

    public function testWithPeerName(): void
    {
        $config = ClientConfig::default()->withPeerName('example.com');

        static::assertSame('example.com', $config->peerName);
    }

    public function testWithPeerVerification(): void
    {
        $config = ClientConfig::default()->withPeerVerification(false);

        static::assertFalse($config->peerVerification);
    }

    public function testWithAllowSelfSigned(): void
    {
        $config = ClientConfig::default()->withAllowSelfSigned(true);

        static::assertTrue($config->allowSelfSigned);
    }

    public function testWithCertificateAuthority(): void
    {
        $config = ClientConfig::default()->withCertificateAuthority('/path/to/ca.pem');

        static::assertSame('/path/to/ca.pem', $config->certificateAuthority);
    }

    public function testWithCertificateAuthorityPath(): void
    {
        $config = ClientConfig::default()->withCertificateAuthorityPath('/path/to/ca/');

        static::assertSame('/path/to/ca/', $config->certificateAuthorityPath);
    }

    public function testWithCertificate(): void
    {
        $cert = Certificate::create('/cert.pem', '/key.pem');
        $config = ClientConfig::default()->withCertificate($cert);

        static::assertSame($cert, $config->certificate);
    }

    public function testWithMinimumVersion(): void
    {
        $config = ClientConfig::default()->withMinimumVersion(Version::Tls12);

        static::assertSame(Version::Tls12, $config->minimumVersion);
    }

    public function testWithMaximumVersion(): void
    {
        $config = ClientConfig::default()->withMaximumVersion(Version::Tls13);

        static::assertSame(Version::Tls13, $config->maximumVersion);
    }

    public function testWithCiphers(): void
    {
        $config = ClientConfig::default()->withCiphers('ECDHE-RSA-AES128-GCM-SHA256');

        static::assertSame('ECDHE-RSA-AES128-GCM-SHA256', $config->ciphers);
    }

    public function testWithSecurityLevel(): void
    {
        $config = ClientConfig::default()->withSecurityLevel(3);

        static::assertSame(3, $config->securityLevel);
    }

    public function testWithAlpnProtocols(): void
    {
        $config = ClientConfig::default()->withAlpnProtocols(['h2', 'http/1.1']);

        static::assertSame(['h2', 'http/1.1'], $config->alpnProtocols);
    }

    public function testWithAlpnProtocolsNull(): void
    {
        $config = ClientConfig::default()->withAlpnProtocols(['h2'])->withAlpnProtocols(null);

        static::assertNull($config->alpnProtocols);
    }

    public function testWithSessionTickets(): void
    {
        $config = ClientConfig::default()->withSessionTickets(false);

        static::assertFalse($config->sessionTickets);
    }

    public function testImmutability(): void
    {
        $original = ClientConfig::default();
        $modified = $original->withPeerName('example.com');

        static::assertNull($original->peerName);
        static::assertSame('example.com', $modified->peerName);
        static::assertNotSame($original, $modified);
    }

    public function testChaining(): void
    {
        $cert = Certificate::create('/cert.pem', '/key.pem');
        $config = ClientConfig::default()
            ->withPeerName('example.com')
            ->withPeerVerification(true)
            ->withAllowSelfSigned(false)
            ->withCertificateAuthority('/ca.pem')
            ->withCertificate($cert)
            ->withMinimumVersion(Version::Tls12)
            ->withMaximumVersion(Version::Tls13)
            ->withSecurityLevel(3)
            ->withAlpnProtocols(['h2', 'http/1.1'])
            ->withSessionTickets(false);

        static::assertSame('example.com', $config->peerName);
        static::assertTrue($config->peerVerification);
        static::assertFalse($config->allowSelfSigned);
        static::assertSame('/ca.pem', $config->certificateAuthority);
        static::assertSame($cert, $config->certificate);
        static::assertSame(Version::Tls12, $config->minimumVersion);
        static::assertSame(Version::Tls13, $config->maximumVersion);
        static::assertSame(3, $config->securityLevel);
        static::assertSame(['h2', 'http/1.1'], $config->alpnProtocols);
        static::assertFalse($config->sessionTickets);
    }
}
