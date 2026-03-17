<?php

declare(strict_types=1);

namespace Psl\TLS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\TLS\Certificate;
use Psl\TLS\ServerConfiguration;
use Psl\TLS\Version;

final class ServerOptionsTest extends TestCase
{
    private static function certificate(): Certificate
    {
        return Certificate::create('/cert.pem', '/key.pem');
    }

    public function testCreate(): void
    {
        $cert = self::certificate();
        $config = ServerConfiguration::create($cert);

        static::assertSame($cert, $config->certificate);
        static::assertNull($config->minimumVersion);
        static::assertNull($config->maximumVersion);
        static::assertNull($config->ciphers);
        static::assertSame(2, $config->securityLevel);
        static::assertNull($config->certificateAuthority);
        static::assertNull($config->certificateAuthorityPath);
        static::assertNull($config->alpnProtocols);
        static::assertSame([], $config->sniCertificates);
        static::assertTrue($config->sessionTickets);
    }

    public function testWithCertificate(): void
    {
        $cert1 = self::certificate();
        $cert2 = Certificate::create('/other-cert.pem', '/other-key.pem');
        $config = ServerConfiguration::create($cert1)->withCertificate($cert2);

        static::assertSame($cert2, $config->certificate);
    }

    public function testWithMinimumVersion(): void
    {
        $config = ServerConfiguration::create(self::certificate())->withMinimumVersion(Version::Tls12);

        static::assertSame(Version::Tls12, $config->minimumVersion);
    }

    public function testWithMaximumVersion(): void
    {
        $config = ServerConfiguration::create(self::certificate())->withMaximumVersion(Version::Tls13);

        static::assertSame(Version::Tls13, $config->maximumVersion);
    }

    public function testWithCiphers(): void
    {
        $config = ServerConfiguration::create(self::certificate())->withCiphers('ECDHE-RSA-AES128-GCM-SHA256');

        static::assertSame('ECDHE-RSA-AES128-GCM-SHA256', $config->ciphers);
    }

    public function testWithSecurityLevel(): void
    {
        $config = ServerConfiguration::create(self::certificate())->withSecurityLevel(4);

        static::assertSame(4, $config->securityLevel);
    }

    public function testWithCertificateAuthority(): void
    {
        $config = ServerConfiguration::create(self::certificate())->withCertificateAuthority('/path/to/ca.pem');

        static::assertSame('/path/to/ca.pem', $config->certificateAuthority);
    }

    public function testWithCertificateAuthorityPath(): void
    {
        $config = ServerConfiguration::create(self::certificate())->withCertificateAuthorityPath('/path/to/ca/');

        static::assertSame('/path/to/ca/', $config->certificateAuthorityPath);
    }

    public function testWithAlpnProtocols(): void
    {
        $config = ServerConfiguration::create(self::certificate())->withAlpnProtocols(['h2', 'http/1.1']);

        static::assertSame(['h2', 'http/1.1'], $config->alpnProtocols);
    }

    public function testWithAlpnProtocolsNull(): void
    {
        $config = ServerConfiguration::create(self::certificate())->withAlpnProtocols(['h2'])->withAlpnProtocols(null);

        static::assertNull($config->alpnProtocols);
    }

    public function testWithSniCertificate(): void
    {
        $cert = Certificate::create('/sni-cert.pem', '/sni-key.pem');
        $config = ServerConfiguration::create(self::certificate())->withSniCertificate('example.com', $cert);

        static::assertArrayHasKey('example.com', $config->sniCertificates);
        static::assertSame($cert, $config->sniCertificates['example.com']);
    }

    public function testWithSniCertificates(): void
    {
        $cert1 = Certificate::create('/sni1-cert.pem', '/sni1-key.pem');
        $cert2 = Certificate::create('/sni2-cert.pem', '/sni2-key.pem');
        $sni = ['a.example.com' => $cert1, 'b.example.com' => $cert2];
        $config = ServerConfiguration::create(self::certificate())->withSniCertificates($sni);

        static::assertSame($sni, $config->sniCertificates);
    }

    public function testWithSessionTickets(): void
    {
        $config = ServerConfiguration::create(self::certificate())->withSessionTickets(false);

        static::assertFalse($config->sessionTickets);
    }

    public function testImmutability(): void
    {
        $original = ServerConfiguration::create(self::certificate());
        $modified = $original->withMinimumVersion(Version::Tls12);

        static::assertNull($original->minimumVersion);
        static::assertSame(Version::Tls12, $modified->minimumVersion);
        static::assertNotSame($original, $modified);
    }
}
