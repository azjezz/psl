<?php

declare(strict_types=1);

namespace Psl\TLS\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psl\TLS\ConnectionState;
use Psl\TLS\Version;

final class ConnectionStateTest extends TestCase
{
    public function testConstruction(): void
    {
        $state = new ConnectionState(
            version: Version::Tls13,
            cipherName: 'TLS_AES_256_GCM_SHA384',
            cipherBits: 256,
            cipherVersion: 'TLSv1.3',
            alpnProtocol: 'h2',
            peerCertificate: null,
            peerCertificateChain: null,
        );

        static::assertSame(Version::Tls13, $state->version);
        static::assertSame('TLS_AES_256_GCM_SHA384', $state->cipherName);
        static::assertSame(256, $state->cipherBits);
        static::assertSame('TLSv1.3', $state->cipherVersion);
        static::assertSame('h2', $state->alpnProtocol);
        static::assertNull($state->peerCertificate);
        static::assertNull($state->peerCertificateChain);
    }

    public function testConstructionWithNullAlpn(): void
    {
        $state = new ConnectionState(
            version: Version::Tls12,
            cipherName: 'ECDHE-RSA-AES128-GCM-SHA256',
            cipherBits: 128,
            cipherVersion: 'TLSv1.2',
            alpnProtocol: null,
            peerCertificate: null,
            peerCertificateChain: null,
        );

        static::assertSame(Version::Tls12, $state->version);
        static::assertNull($state->alpnProtocol);
    }
}
