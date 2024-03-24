<?php

declare(strict_types=1);

namespace Psl\TCP\TLS;

use Psl\Default\DefaultInterface;

/**
 * ```
 * $tlsOptions = TCP\TLS\ServerOptions::create()
 *     ->withMinimumVersion(TCP\TLS\Version::Tls12)
 *     ->withVerifyPeer(true)
 *     ->withCertificateAuthorityFile('/path/to/cafile.pem');
 * ```.
 *
 * @immutable
 */
final class ServerOptions implements DefaultInterface
{
    /**
     * Constructs a new instance of the TLS server options with specified settings.
     *
     * @param Version $minimumVersion Specifies the minimum TLS version that is acceptable for negotiation.
     * @param string $peerName Specifies the expected name of the peer, used in verifying the peer's certificate.
     * @param bool $peerVerification Indicates whether the peer's SSL certificate should be verified.
     * @param int<0, max> $verificationDepth Specifies the maximum depth for certificate chain verification.
     * @param null|non-empty-string $ciphers Specifies the cipher suite(s) to be used for the TLS server, determining
     *                                       the encryption algorithms that will be available during the TLS handshake.
     * @param null|non-empty-string $certificateAuthorityFile Optional. Specifies the path to a Certificate Authority (CA) file
     *                                                        to be used for verifying the peer's certificate.
     * @param null|non-empty-string $certificateAuthorityPath Optional. Specifies the path to a directory containing Certificate
     *                                                        Authority (CA) certificates, which will be used for verifying the peer's certificate.
     * @param bool $capturePeerCertificate Indicates whether the peer's certificate should be captured during
     *                                     the handshake process. This can be useful for inspection or logging purposes.
     * @param SecurityLevel $securityLevel Specifies the security level for the TLS server, influencing
     *                                     the choice of cryptographic algorithms.
     * @param array<string, Certificate> $certificates
     * @param null|Certificate $defaultCertificate Optional.
     * @param list<non-empty-string> $alpnProtocols Specifies the protocols to be used for Application Layer Protocol Negotiation (ALPN),
     *                                              enabling the selection of application-specific protocols within the TLS layer.
     *
     * @pure
     */
    private function __construct(
        public readonly Version       $minimumVersion,
        public readonly string        $peerName,
        public readonly bool          $peerVerification,
        public readonly bool          $allowSelfSignedCertificates,
        public readonly int           $verificationDepth,
        public readonly ?string       $ciphers,
        public readonly ?string       $certificateAuthorityFile,
        public readonly ?string       $certificateAuthorityPath,
        public readonly bool          $capturePeerCertificate,
        public readonly SecurityLevel $securityLevel,
        public readonly array         $certificates,
        public readonly ?Certificate  $defaultCertificate,
        public readonly array         $alpnProtocols,
    ) {
    }

    /**
     * Creates a new instance of {@see ServerOptions} with default settings.
     *
     * @return ServerOptions The new instance with default values.
     *
     * @pure
     */
    public static function create(): self
    {
        return new self(
            minimumVersion: Version::default(),
            peerName: '',
            peerVerification: true,
            allowSelfSignedCertificates: true,
            verificationDepth: 10,
            ciphers: null,
            certificateAuthorityFile: null,
            certificateAuthorityPath: null,
            capturePeerCertificate: false,
            securityLevel: SecurityLevel::default(),
            certificates: [],
            defaultCertificate: null,
            alpnProtocols: []
        );
    }

    /**
     * Creates and returns a default instance of {@see ClientOptions}.
     *
     * @pure
     */
    public static function default(): static
    {
        return static::create();
    }

    /**
     * Specifies the minimum version of the TLS protocol to negotiate.
     *
     * @param Version $version The minimum TLS version.
     *
     * @return ServerOptions A new instance with the specified minimum TLS version.
     *
     * @mutation-free
     */
    public function withMinimumVersion(Version $version): self
    {
        return new self(
            $version,
            $this->peerName,
            $this->peerVerification,
            $this->allowSelfSignedCertificates,
            $this->verificationDepth,
            $this->ciphers,
            $this->certificateAuthorityFile,
            $this->certificateAuthorityPath,
            $this->capturePeerCertificate,
            $this->securityLevel,
            $this->certificates,
            $this->defaultCertificate,
            $this->alpnProtocols
        );
    }

    /**
     * Sets the expected name of the peer for certificate verification.
     *
     * @param string $peer_name The expected name of the peer.
     *
     * @return ServerOptions A new instance with the specified peer name.
     *
     * @mutation-free
     */
    public function withPeerName(string $peer_name): self
    {
        return new self(
            $this->minimumVersion,
            $peer_name,
            $this->peerVerification,
            $this->allowSelfSignedCertificates,
            $this->verificationDepth,
            $this->ciphers,
            $this->certificateAuthorityFile,
            $this->certificateAuthorityPath,
            $this->capturePeerCertificate,
            $this->securityLevel,
            $this->certificates,
            $this->defaultCertificate,
            $this->alpnProtocols
        );
    }

    /**
     * Enables verification of the peer's SSL certificate.
     *
     * @return ServerOptions A new instance with the peer verification option modified.
     *
     * @mutation-free
     */
    public function withPeerVerification(bool $peer_verification = true): self
    {
        return new self(
            $this->minimumVersion,
            $this->peerName,
            $peer_verification,
            $this->allowSelfSignedCertificates,
            $this->verificationDepth,
            $this->ciphers,
            $this->certificateAuthorityFile,
            $this->certificateAuthorityPath,
            $this->capturePeerCertificate,
            $this->securityLevel,
            $this->certificates,
            $this->defaultCertificate,
            $this->alpnProtocols
        );
    }

    /**
     * Allow self-signed certificates.
     *
     * @return ServerOptions A new instance with to allow self-signed certificate option modified.
     *
     * @mutation-free
     */
    public function withAllowSelfSignedCertificates(bool $allow_self_signed_certificates = true): self
    {
        return new self(
            $this->minimumVersion,
            $this->peerName,
            $this->peerVerification,
            $allow_self_signed_certificates,
            $this->verificationDepth,
            $this->ciphers,
            $this->certificateAuthorityFile,
            $this->certificateAuthorityPath,
            $this->capturePeerCertificate,
            $this->securityLevel,
            $this->certificates,
            $this->defaultCertificate,
            $this->alpnProtocols
        );
    }

    /**
     * Sets the maximum depth for certificate chain verification.
     *
     * @param int<0, max> $verification_depth The maximum verification depth.
     *
     * @return ServerOptions A new instance with the specified verification depth.
     *
     * @mutation-free
     */
    public function withVerificationDepth(int $verification_depth): self
    {
        return new self(
            $this->minimumVersion,
            $this->peerName,
            $this->peerVerification,
            $this->allowSelfSignedCertificates,
            $verification_depth,
            $this->ciphers,
            $this->certificateAuthorityFile,
            $this->certificateAuthorityPath,
            $this->capturePeerCertificate,
            $this->securityLevel,
            $this->certificates,
            $this->defaultCertificate,
            $this->alpnProtocols
        );
    }

    /**
     * Specifies the cipher suite to be used for the TLS server.
     *
     * @param non-empty-string $ciphers The cipher suite.
     *
     * @return ServerOptions A new instance with the specified ciphers.
     *
     * @mutation-free
     */
    public function withCiphers(string $ciphers): self
    {
        return new self(
            $this->minimumVersion,
            $this->peerName,
            $this->peerVerification,
            $this->allowSelfSignedCertificates,
            $this->verificationDepth,
            $ciphers,
            $this->certificateAuthorityFile,
            $this->certificateAuthorityPath,
            $this->capturePeerCertificate,
            $this->securityLevel,
            $this->certificates,
            $this->defaultCertificate,
            $this->alpnProtocols
        );
    }

    /**
     * Sets the path to the Certificate Authority (CA) file for verifying the peer certificate.
     *
     * @param null|non-empty-string $certificate_authority_file The path to the CA file.
     *
     * @return ServerOptions A new instance with the specified CA file path.
     *
     * @mutation-free
     */
    public function withCertificateAuthorityFile(?string $certificate_authority_file): self
    {
        return new self(
            $this->minimumVersion,
            $this->peerName,
            $this->peerVerification,
            $this->allowSelfSignedCertificates,
            $this->verificationDepth,
            $this->ciphers,
            $certificate_authority_file,
            $this->certificateAuthorityPath,
            $this->capturePeerCertificate,
            $this->securityLevel,
            $this->certificates,
            $this->defaultCertificate,
            $this->alpnProtocols
        );
    }

    /**
     * Sets the path to the Certificate Authority (CA) directory for verifying the peer certificate.
     *
     * @param null|non-empty-string $certificate_authority_path The path to the CA directory.
     *
     * @return ServerOptions A new instance with the specified CA directory path.
     *
     * @mutation-free
     */
    public function withCertificateAuthorityPath(?string $certificate_authority_path): self
    {
        return new self(
            $this->minimumVersion,
            $this->peerName,
            $this->peerVerification,
            $this->allowSelfSignedCertificates,
            $this->verificationDepth,
            $this->ciphers,
            $this->certificateAuthorityFile,
            $certificate_authority_path,
            $this->capturePeerCertificate,
            $this->securityLevel,
            $this->certificates,
            $this->defaultCertificate,
            $this->alpnProtocols
        );
    }

    /**
     * Enables or disables capturing of the peer's certificate.
     *
     * @param bool $capture_peer_certificate Whether to capture the peer's certificate.
     *
     * @return ServerOptions A new instance with the specified peer certificate capturing setting.
     *
     * @mutation-free
     */
    public function withCapturePeerCertificate(bool $capture_peer_certificate): self
    {
        return new self(
            $this->minimumVersion,
            $this->peerName,
            $this->peerVerification,
            $this->allowSelfSignedCertificates,
            $this->verificationDepth,
            $this->ciphers,
            $this->certificateAuthorityFile,
            $this->certificateAuthorityPath,
            $capture_peer_certificate,
            $this->securityLevel,
            $this->certificates,
            $this->defaultCertificate,
            $this->alpnProtocols
        );
    }

    /**
     * Sets the security level for the TLS server.
     *
     * @param SecurityLevel $security_level The security level.
     *
     * @return ServerOptions A new instance with the specified security level.
     *
     * @mutation-free
     */
    public function withSecurityLevel(SecurityLevel $security_level): self
    {
        return new self(
            $this->minimumVersion,
            $this->peerName,
            $this->peerVerification,
            $this->allowSelfSignedCertificates,
            $this->verificationDepth,
            $this->ciphers,
            $this->certificateAuthorityFile,
            $this->certificateAuthorityPath,
            $this->capturePeerCertificate,
            $security_level,
            $this->certificates,
            $this->defaultCertificate,
            $this->alpnProtocols
        );
    }

    /**
     * @param array<string, Certificate> $certificates
     *
     * @return ServerOptions A new instance with the specified certificates.
     *
     * @mutation-free
     */
    public function withCertificates(array $certificates): self
    {
        return new self(
            $this->minimumVersion,
            $this->peerName,
            $this->peerVerification,
            $this->allowSelfSignedCertificates,
            $this->verificationDepth,
            $this->ciphers,
            $this->certificateAuthorityFile,
            $this->certificateAuthorityPath,
            $this->capturePeerCertificate,
            $this->securityLevel,
            $certificates,
            $this->defaultCertificate,
            $this->alpnProtocols
        );
    }

    /**
     * Specifies a Certificate to be used for the TLS server.
     *
     * @param null|Certificate $default_certificate The certificate.
     *
     * @return ServerOptions A new instance with the specified certificate.
     *
     * @mutation-free
     */
    public function withDefaultCertificate(?Certificate $default_certificate): self
    {
        return new self(
            $this->minimumVersion,
            $this->peerName,
            $this->peerVerification,
            $this->allowSelfSignedCertificates,
            $this->verificationDepth,
            $this->ciphers,
            $this->certificateAuthorityFile,
            $this->certificateAuthorityPath,
            $this->capturePeerCertificate,
            $this->securityLevel,
            $this->certificates,
            $default_certificate,
            $this->alpnProtocols
        );
    }

    /**
     * Sets the protocols to be used for Application Layer Protocol Negotiation (ALPN).
     *
     * @param list<non-empty-string> $alpn_protocols The ALPN protocols.
     *
     * @return ServerOptions A new instance with the specified ALPN protocols.
     *
     * @mutation-free
     */
    public function withAlpnProtocols(array $alpn_protocols): self
    {
        return new self(
            $this->minimumVersion,
            $this->peerName,
            $this->peerVerification,
            $this->allowSelfSignedCertificates,
            $this->verificationDepth,
            $this->ciphers,
            $this->certificateAuthorityFile,
            $this->certificateAuthorityPath,
            $this->capturePeerCertificate,
            $this->securityLevel,
            $this->certificates,
            $this->defaultCertificate,
            $alpn_protocols
        );
    }
}
