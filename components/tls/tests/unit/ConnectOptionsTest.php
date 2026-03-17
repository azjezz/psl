<?php

declare(strict_types=1);

namespace Psl\TLS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\TLS\Certificate;
use Psl\TLS\ClientConfiguration;
use Psl\TLS\Version;

final class ConnectOptionsTest extends TestCase
{
    public function testDefault(): void
    {
        $config = ClientConfiguration::default();

        static::assertNull($config->peerName);
        static::assertTrue($config->peerVerification);
        static::assertNull($config->peerNameVerification);
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
        static::assertNull($config->peerFingerprints);
        static::assertTrue($config->sniEnabled);
        static::assertSame(10, $config->verificationDepth);
    }

    public function testWithPeerName(): void
    {
        $config = ClientConfiguration::default()->withPeerName('example.com');

        static::assertSame('example.com', $config->peerName);
    }

    public function testWithPeerVerification(): void
    {
        $config = ClientConfiguration::default()->withPeerVerification(false);

        static::assertFalse($config->peerVerification);
    }

    public function testWithPeerVerificationDefaultsToTrue(): void
    {
        $config = ClientConfiguration::default()->withPeerVerification(false)->withPeerVerification();

        static::assertTrue($config->peerVerification);
    }

    public function testWithPeerNameVerification(): void
    {
        $config = ClientConfiguration::default()->withPeerNameVerification(false);

        static::assertFalse($config->peerNameVerification);
    }

    public function testWithPeerNameVerificationNull(): void
    {
        $config = ClientConfiguration::default()->withPeerNameVerification(false)->withPeerNameVerification(null);

        static::assertNull($config->peerNameVerification);
    }

    public function testWithAllowSelfSigned(): void
    {
        $config = ClientConfiguration::default()->withAllowSelfSigned(true);

        static::assertTrue($config->allowSelfSigned);
    }

    public function testWithAllowSelfSignedDefaultsToTrue(): void
    {
        $config = ClientConfiguration::default()->withAllowSelfSigned();

        static::assertTrue($config->allowSelfSigned);
    }

    public function testWithCertificateAuthority(): void
    {
        $config = ClientConfiguration::default()->withCertificateAuthority('/path/to/ca.pem');

        static::assertSame('/path/to/ca.pem', $config->certificateAuthority);
    }

    public function testWithCertificateAuthorityPath(): void
    {
        $config = ClientConfiguration::default()->withCertificateAuthorityPath('/path/to/ca/');

        static::assertSame('/path/to/ca/', $config->certificateAuthorityPath);
    }

    public function testWithCertificate(): void
    {
        $cert = Certificate::create('/cert.pem', '/key.pem');
        $config = ClientConfiguration::default()->withCertificate($cert);

        static::assertSame($cert, $config->certificate);
    }

    public function testWithMinimumVersion(): void
    {
        $config = ClientConfiguration::default()->withMinimumVersion(Version::Tls12);

        static::assertSame(Version::Tls12, $config->minimumVersion);
    }

    public function testWithMaximumVersion(): void
    {
        $config = ClientConfiguration::default()->withMaximumVersion(Version::Tls13);

        static::assertSame(Version::Tls13, $config->maximumVersion);
    }

    public function testWithCiphers(): void
    {
        $config = ClientConfiguration::default()->withCiphers('ECDHE-RSA-AES128-GCM-SHA256');

        static::assertSame('ECDHE-RSA-AES128-GCM-SHA256', $config->ciphers);
    }

    public function testWithSecurityLevel(): void
    {
        $config = ClientConfiguration::default()->withSecurityLevel(3);

        static::assertSame(3, $config->securityLevel);
    }

    public function testWithAlpnProtocols(): void
    {
        $config = ClientConfiguration::default()->withAlpnProtocols(['h2', 'http/1.1']);

        static::assertSame(['h2', 'http/1.1'], $config->alpnProtocols);
    }

    public function testWithAlpnProtocolsNull(): void
    {
        $config = ClientConfiguration::default()->withAlpnProtocols(['h2'])->withAlpnProtocols(null);

        static::assertNull($config->alpnProtocols);
    }

    public function testWithSessionTickets(): void
    {
        $config = ClientConfiguration::default()->withSessionTickets(false);

        static::assertFalse($config->sessionTickets);
    }

    public function testImmutability(): void
    {
        $original = ClientConfiguration::default();
        $modified = $original->withPeerName('example.com');

        static::assertNull($original->peerName);
        static::assertSame('example.com', $modified->peerName);
        static::assertNotSame($original, $modified);
    }

    public function testWithPeerFingerprints(): void
    {
        $fingerprints = [
            'a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2',
        ];
        $config = ClientConfiguration::default()->withPeerFingerprints($fingerprints);

        static::assertSame($fingerprints, $config->peerFingerprints);
    }

    public function testWithPeerFingerprintsMultiple(): void
    {
        $fingerprints = [
            'a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2',
            'f6e5d4c3b2a1f6e5d4c3b2a1f6e5d4c3b2a1f6e5d4c3b2a1f6e5d4c3b2a1f6e5',
        ];
        $config = ClientConfiguration::default()->withPeerFingerprints($fingerprints);

        static::assertSame($fingerprints, $config->peerFingerprints);
    }

    public function testWithPeerFingerprintsNull(): void
    {
        $config = ClientConfiguration::default()->withPeerFingerprints(['abc123'])->withPeerFingerprints(null);

        static::assertNull($config->peerFingerprints);
    }

    public function testWithSniEnabled(): void
    {
        $config = ClientConfiguration::default()->withSniEnabled(false);

        static::assertFalse($config->sniEnabled);
    }

    public function testWithSniEnabledDefaultsToTrue(): void
    {
        $config = ClientConfiguration::default()->withSniEnabled(false)->withSniEnabled();

        static::assertTrue($config->sniEnabled);
    }

    public function testWithVerificationDepth(): void
    {
        $config = ClientConfiguration::default()->withVerificationDepth(5);

        static::assertSame(5, $config->verificationDepth);
    }

    public function testChaining(): void
    {
        $cert = Certificate::create('/cert.pem', '/key.pem');
        $config = ClientConfiguration::default()
            ->withPeerName('example.com')
            ->withPeerVerification(true)
            ->withPeerNameVerification(false)
            ->withAllowSelfSigned(false)
            ->withCertificateAuthority('/ca.pem')
            ->withCertificate($cert)
            ->withMinimumVersion(Version::Tls12)
            ->withMaximumVersion(Version::Tls13)
            ->withSecurityLevel(3)
            ->withAlpnProtocols(['h2', 'http/1.1'])
            ->withSessionTickets(false)
            ->withPeerFingerprints(['abcdef1234567890'])
            ->withSniEnabled(false)
            ->withVerificationDepth(20);

        static::assertSame('example.com', $config->peerName);
        static::assertTrue($config->peerVerification);
        static::assertFalse($config->peerNameVerification);
        static::assertFalse($config->allowSelfSigned);
        static::assertSame('/ca.pem', $config->certificateAuthority);
        static::assertSame($cert, $config->certificate);
        static::assertSame(Version::Tls12, $config->minimumVersion);
        static::assertSame(Version::Tls13, $config->maximumVersion);
        static::assertSame(3, $config->securityLevel);
        static::assertSame(['h2', 'http/1.1'], $config->alpnProtocols);
        static::assertFalse($config->sessionTickets);
        static::assertSame(['abcdef1234567890'], $config->peerFingerprints);
        static::assertFalse($config->sniEnabled);
        static::assertSame(20, $config->verificationDepth);
    }
}
