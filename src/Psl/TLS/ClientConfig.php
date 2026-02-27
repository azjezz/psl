<?php

declare(strict_types=1);

namespace Psl\TLS;

use Override;
use Psl\Default\DefaultInterface;

/**
 * TLS configuration for client connections.
 *
 * Used with {@see Connector} to perform TLS handshakes.
 *
 * @psalm-immutable
 */
final readonly class ClientConfig implements DefaultInterface
{
    /**
     * @param ?non-empty-string $peerName SNI hostname for the TLS handshake. When null, the connection host is used.
     * @param bool $peerVerification Whether to verify the peer certificate.
     * @param bool $allowSelfSigned Whether to allow self-signed certificates.
     * @param ?non-empty-string $certificateAuthority Path to a CA file for peer verification.
     * @param ?non-empty-string $certificateAuthorityPath Path to a directory of CA files for peer verification.
     * @param ?Certificate $certificate Client certificate for mutual TLS authentication.
     * @param ?Version $minimumVersion Minimum TLS protocol version to accept.
     * @param ?Version $maximumVersion Maximum TLS protocol version to accept.
     * @param ?non-empty-string $ciphers OpenSSL cipher string to use.
     * @param int<0, 5> $securityLevel OpenSSL security level.
     * @param ?list<non-empty-string> $alpnProtocols ALPN protocol list (e.g. ['h2', 'http/1.1']).
     * @param bool $sessionTickets Whether to enable TLS session tickets for session resumption.
     *
     * @psalm-mutation-free
     *
     * @mago-expect lint:excessive-parameter-list
     */
    public function __construct(
        public null|string $peerName = null,
        public bool $peerVerification = true,
        public bool $allowSelfSigned = false,
        public null|string $certificateAuthority = null,
        public null|string $certificateAuthorityPath = null,
        public null|Certificate $certificate = null,
        public null|Version $minimumVersion = null,
        public null|Version $maximumVersion = null,
        public null|string $ciphers = null,
        public int $securityLevel = 2,
        /** @var null|list<non-empty-string> */
        public null|array $alpnProtocols = null,
        public bool $sessionTickets = true,
    ) {}

    /**
     * @pure
     */
    #[Override]
    public static function default(): static
    {
        return new self();
    }

    /**
     * @param ?non-empty-string $peer_name SNI hostname for the TLS handshake.
     *
     * @psalm-mutation-free
     */
    public function withPeerName(null|string $peer_name): self
    {
        return new self(
            $peer_name,
            $this->peerVerification,
            $this->allowSelfSigned,
            $this->certificateAuthority,
            $this->certificateAuthorityPath,
            $this->certificate,
            $this->minimumVersion,
            $this->maximumVersion,
            $this->ciphers,
            $this->securityLevel,
            $this->alpnProtocols,
            $this->sessionTickets,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function withPeerVerification(bool $enabled = true): self
    {
        return new self(
            $this->peerName,
            $enabled,
            $this->allowSelfSigned,
            $this->certificateAuthority,
            $this->certificateAuthorityPath,
            $this->certificate,
            $this->minimumVersion,
            $this->maximumVersion,
            $this->ciphers,
            $this->securityLevel,
            $this->alpnProtocols,
            $this->sessionTickets,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function withAllowSelfSigned(bool $enabled = true): self
    {
        return new self(
            $this->peerName,
            $this->peerVerification,
            $enabled,
            $this->certificateAuthority,
            $this->certificateAuthorityPath,
            $this->certificate,
            $this->minimumVersion,
            $this->maximumVersion,
            $this->ciphers,
            $this->securityLevel,
            $this->alpnProtocols,
            $this->sessionTickets,
        );
    }

    /**
     * @param ?non-empty-string $certificate_authority Path to a CA file.
     *
     * @psalm-mutation-free
     */
    public function withCertificateAuthority(null|string $certificate_authority): self
    {
        return new self(
            $this->peerName,
            $this->peerVerification,
            $this->allowSelfSigned,
            $certificate_authority,
            $this->certificateAuthorityPath,
            $this->certificate,
            $this->minimumVersion,
            $this->maximumVersion,
            $this->ciphers,
            $this->securityLevel,
            $this->alpnProtocols,
            $this->sessionTickets,
        );
    }

    /**
     * @param ?non-empty-string $certificate_authority_path Path to a CA directory.
     *
     * @psalm-mutation-free
     */
    public function withCertificateAuthorityPath(null|string $certificate_authority_path): self
    {
        return new self(
            $this->peerName,
            $this->peerVerification,
            $this->allowSelfSigned,
            $this->certificateAuthority,
            $certificate_authority_path,
            $this->certificate,
            $this->minimumVersion,
            $this->maximumVersion,
            $this->ciphers,
            $this->securityLevel,
            $this->alpnProtocols,
            $this->sessionTickets,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function withCertificate(null|Certificate $certificate): self
    {
        return new self(
            $this->peerName,
            $this->peerVerification,
            $this->allowSelfSigned,
            $this->certificateAuthority,
            $this->certificateAuthorityPath,
            $certificate,
            $this->minimumVersion,
            $this->maximumVersion,
            $this->ciphers,
            $this->securityLevel,
            $this->alpnProtocols,
            $this->sessionTickets,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function withMinimumVersion(null|Version $minimum_version): self
    {
        return new self(
            $this->peerName,
            $this->peerVerification,
            $this->allowSelfSigned,
            $this->certificateAuthority,
            $this->certificateAuthorityPath,
            $this->certificate,
            $minimum_version,
            $this->maximumVersion,
            $this->ciphers,
            $this->securityLevel,
            $this->alpnProtocols,
            $this->sessionTickets,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function withMaximumVersion(null|Version $maximum_version): self
    {
        return new self(
            $this->peerName,
            $this->peerVerification,
            $this->allowSelfSigned,
            $this->certificateAuthority,
            $this->certificateAuthorityPath,
            $this->certificate,
            $this->minimumVersion,
            $maximum_version,
            $this->ciphers,
            $this->securityLevel,
            $this->alpnProtocols,
            $this->sessionTickets,
        );
    }

    /**
     * @param ?non-empty-string $ciphers OpenSSL cipher string.
     *
     * @psalm-mutation-free
     */
    public function withCiphers(null|string $ciphers): self
    {
        return new self(
            $this->peerName,
            $this->peerVerification,
            $this->allowSelfSigned,
            $this->certificateAuthority,
            $this->certificateAuthorityPath,
            $this->certificate,
            $this->minimumVersion,
            $this->maximumVersion,
            $ciphers,
            $this->securityLevel,
            $this->alpnProtocols,
            $this->sessionTickets,
        );
    }

    /**
     * @param int<0, 5> $security_level OpenSSL security level.
     *
     * @psalm-mutation-free
     */
    public function withSecurityLevel(int $security_level): self
    {
        return new self(
            $this->peerName,
            $this->peerVerification,
            $this->allowSelfSigned,
            $this->certificateAuthority,
            $this->certificateAuthorityPath,
            $this->certificate,
            $this->minimumVersion,
            $this->maximumVersion,
            $this->ciphers,
            $security_level,
            $this->alpnProtocols,
            $this->sessionTickets,
        );
    }

    /**
     * @param ?list<non-empty-string> $alpn_protocols ALPN protocol list (e.g. ['h2', 'http/1.1']).
     *
     * @psalm-mutation-free
     */
    public function withAlpnProtocols(null|array $alpn_protocols): self
    {
        return new self(
            $this->peerName,
            $this->peerVerification,
            $this->allowSelfSigned,
            $this->certificateAuthority,
            $this->certificateAuthorityPath,
            $this->certificate,
            $this->minimumVersion,
            $this->maximumVersion,
            $this->ciphers,
            $this->securityLevel,
            $alpn_protocols,
            $this->sessionTickets,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function withSessionTickets(bool $enabled): self
    {
        return new self(
            $this->peerName,
            $this->peerVerification,
            $this->allowSelfSigned,
            $this->certificateAuthority,
            $this->certificateAuthorityPath,
            $this->certificate,
            $this->minimumVersion,
            $this->maximumVersion,
            $this->ciphers,
            $this->securityLevel,
            $this->alpnProtocols,
            $enabled,
        );
    }
}
