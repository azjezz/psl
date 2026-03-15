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
final readonly class ClientConfiguration implements DefaultInterface
{
    /**
     * @param ?non-empty-string $peerName SNI hostname for the TLS handshake. When null, the connection host is used.
     * @param bool $peerVerification Whether to verify the peer certificate.
     * @param bool $peerNameVerification Whether to verify the peer name matches the certificate. When null, follows $peerVerification.
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
     * @param ?list<non-empty-string> $peerFingerprints SHA-256 hex fingerprints for certificate pinning. Any match is accepted.
     * @param bool $sniEnabled Whether to enable Server Name Indication (SNI).
     * @param int<1, 100> $verificationDepth Maximum certificate chain verification depth.
     *
     * @psalm-mutation-free
     *
     * @mago-expect lint:excessive-parameter-list
     */
    public function __construct(
        public null|string $peerName = null,
        public bool $peerVerification = true,
        public null|bool $peerNameVerification = null,
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
        /** @var null|list<non-empty-string> */
        public null|array $peerFingerprints = null,
        public bool $sniEnabled = true,
        public int $verificationDepth = 10,
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
     * @param ?non-empty-string $peerName SNI hostname for the TLS handshake.
     *
     * @psalm-mutation-free
     */
    public function withPeerName(null|string $peerName): self
    {
        return new self(
            $peerName,
            $this->peerVerification,
            $this->peerNameVerification,
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
            $this->peerFingerprints,
            $this->sniEnabled,
            $this->verificationDepth,
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
            $this->peerNameVerification,
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
            $this->peerFingerprints,
            $this->sniEnabled,
            $this->verificationDepth,
        );
    }

    /**
     * Whether to verify the peer name matches the certificate.
     *
     * When null, follows the value of $peerVerification.
     *
     * @psalm-mutation-free
     */
    public function withPeerNameVerification(null|bool $enabled): self
    {
        return new self(
            $this->peerName,
            $this->peerVerification,
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
            $this->peerFingerprints,
            $this->sniEnabled,
            $this->verificationDepth,
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
            $this->peerNameVerification,
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
            $this->peerFingerprints,
            $this->sniEnabled,
            $this->verificationDepth,
        );
    }

    /**
     * @param ?non-empty-string $certificateAuthority Path to a CA file.
     *
     * @psalm-mutation-free
     */
    public function withCertificateAuthority(null|string $certificateAuthority): self
    {
        return new self(
            $this->peerName,
            $this->peerVerification,
            $this->peerNameVerification,
            $this->allowSelfSigned,
            $certificateAuthority,
            $this->certificateAuthorityPath,
            $this->certificate,
            $this->minimumVersion,
            $this->maximumVersion,
            $this->ciphers,
            $this->securityLevel,
            $this->alpnProtocols,
            $this->sessionTickets,
            $this->peerFingerprints,
            $this->sniEnabled,
            $this->verificationDepth,
        );
    }

    /**
     * @param ?non-empty-string $certificateAuthorityPath Path to a CA directory.
     *
     * @psalm-mutation-free
     */
    public function withCertificateAuthorityPath(null|string $certificateAuthorityPath): self
    {
        return new self(
            $this->peerName,
            $this->peerVerification,
            $this->peerNameVerification,
            $this->allowSelfSigned,
            $this->certificateAuthority,
            $certificateAuthorityPath,
            $this->certificate,
            $this->minimumVersion,
            $this->maximumVersion,
            $this->ciphers,
            $this->securityLevel,
            $this->alpnProtocols,
            $this->sessionTickets,
            $this->peerFingerprints,
            $this->sniEnabled,
            $this->verificationDepth,
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
            $this->peerNameVerification,
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
            $this->peerFingerprints,
            $this->sniEnabled,
            $this->verificationDepth,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function withMinimumVersion(null|Version $minimumVersion): self
    {
        return new self(
            $this->peerName,
            $this->peerVerification,
            $this->peerNameVerification,
            $this->allowSelfSigned,
            $this->certificateAuthority,
            $this->certificateAuthorityPath,
            $this->certificate,
            $minimumVersion,
            $this->maximumVersion,
            $this->ciphers,
            $this->securityLevel,
            $this->alpnProtocols,
            $this->sessionTickets,
            $this->peerFingerprints,
            $this->sniEnabled,
            $this->verificationDepth,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function withMaximumVersion(null|Version $maximumVersion): self
    {
        return new self(
            $this->peerName,
            $this->peerVerification,
            $this->peerNameVerification,
            $this->allowSelfSigned,
            $this->certificateAuthority,
            $this->certificateAuthorityPath,
            $this->certificate,
            $this->minimumVersion,
            $maximumVersion,
            $this->ciphers,
            $this->securityLevel,
            $this->alpnProtocols,
            $this->sessionTickets,
            $this->peerFingerprints,
            $this->sniEnabled,
            $this->verificationDepth,
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
            $this->peerNameVerification,
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
            $this->peerFingerprints,
            $this->sniEnabled,
            $this->verificationDepth,
        );
    }

    /**
     * @param int<0, 5> $securityLevel OpenSSL security level.
     *
     * @psalm-mutation-free
     */
    public function withSecurityLevel(int $securityLevel): self
    {
        return new self(
            $this->peerName,
            $this->peerVerification,
            $this->peerNameVerification,
            $this->allowSelfSigned,
            $this->certificateAuthority,
            $this->certificateAuthorityPath,
            $this->certificate,
            $this->minimumVersion,
            $this->maximumVersion,
            $this->ciphers,
            $securityLevel,
            $this->alpnProtocols,
            $this->sessionTickets,
            $this->peerFingerprints,
            $this->sniEnabled,
            $this->verificationDepth,
        );
    }

    /**
     * @param ?list<non-empty-string> $alpnProtocols ALPN protocol list (e.g. ['h2', 'http/1.1']).
     *
     * @psalm-mutation-free
     */
    public function withAlpnProtocols(null|array $alpnProtocols): self
    {
        return new self(
            $this->peerName,
            $this->peerVerification,
            $this->peerNameVerification,
            $this->allowSelfSigned,
            $this->certificateAuthority,
            $this->certificateAuthorityPath,
            $this->certificate,
            $this->minimumVersion,
            $this->maximumVersion,
            $this->ciphers,
            $this->securityLevel,
            $alpnProtocols,
            $this->sessionTickets,
            $this->peerFingerprints,
            $this->sniEnabled,
            $this->verificationDepth,
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
            $this->peerNameVerification,
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
            $this->peerFingerprints,
            $this->sniEnabled,
            $this->verificationDepth,
        );
    }

    /**
     * Set SHA-256 hex fingerprints for certificate pinning. Any match is accepted.
     *
     * @param ?list<non-empty-string> $fingerprints SHA-256 hex fingerprints.
     *
     * @psalm-mutation-free
     */
    public function withPeerFingerprints(null|array $fingerprints): self
    {
        return new self(
            $this->peerName,
            $this->peerVerification,
            $this->peerNameVerification,
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
            $fingerprints,
            $this->sniEnabled,
            $this->verificationDepth,
        );
    }

    /**
     * @psalm-mutation-free
     */
    public function withSniEnabled(bool $enabled = true): self
    {
        return new self(
            $this->peerName,
            $this->peerVerification,
            $this->peerNameVerification,
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
            $this->peerFingerprints,
            $enabled,
            $this->verificationDepth,
        );
    }

    /**
     * @param int<1, 100> $depth Maximum certificate chain verification depth.
     *
     * @psalm-mutation-free
     */
    public function withVerificationDepth(int $depth): self
    {
        return new self(
            $this->peerName,
            $this->peerVerification,
            $this->peerNameVerification,
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
            $this->peerFingerprints,
            $this->sniEnabled,
            $depth,
        );
    }
}
