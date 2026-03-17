<?php

declare(strict_types=1);

namespace Psl\TLS;

/**
 * Immutable snapshot of TLS connection state captured after handshake.
 *
 * @psalm-immutable
 */
final readonly class ConnectionState
{
    /**
     * @param Version $version Negotiated TLS protocol version.
     * @param string $cipherName Name of the negotiated cipher (e.g. "TLS_AES_256_GCM_SHA384").
     * @param int $cipherBits Strength of the negotiated cipher in bits (e.g. 256).
     * @param string $cipherVersion Protocol version string of the cipher.
     * @param null|string $alpnProtocol Negotiated ALPN protocol, or null if ALPN was not used.
     * @param null|PeerCertificate $peerCertificate The peer's certificate, or null if not available.
     * @param null|list<PeerCertificate> $peerCertificateChain The peer's certificate chain, or null if not available.
     *
     * @internal
     *
     * @psalm-mutation-free
     */
    public function __construct(
        public Version $version,
        public string $cipherName,
        public int $cipherBits,
        public string $cipherVersion,
        public null|string $alpnProtocol,
        public null|PeerCertificate $peerCertificate,
        /** @var null|list<PeerCertificate> */
        public null|array $peerCertificateChain,
    ) {}
}
