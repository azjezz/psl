<?php

declare(strict_types=1);

namespace Psl\TCP\TLS;

use Psl;
use Psl\Default\DefaultInterface;
use Psl\Str\Byte;

/**
 * {@see ClientOptions} encapsulates all configurable options for establishing a TLS connection.
 *
 * This class provides an immutable data structure for specifying various aspects of a TLS connection,
 * including protocol versions, peer verification, security levels, and more. It follows a fluent interface
 * pattern, allowing for convenient chaining of configuration methods.
 *
 * Instances of {@see ClientOptions} should be created using the static `create()` method, which provides a starting
 * point with sensible defaults. Further customization can be achieved through the `with` and `without` methods,
 * each returning a new instance of the class with the specified modification applied.
 *
 * Example usage:
 *
 * ```
 * $tlsOptions = TCP\TLS\ConnectOptions::create()
 *     ->withMinimumVersion(TCP\TLS\Version::Tls12)
 *     ->withVerifyPeer(true)
 *     ->withCertificateAuthorityFile('/path/to/cafile.pem');
 *
 * ```
 *
 * @immutable
 */
final class ClientOptions implements DefaultInterface
{
    /**
     * Constructs a new instance of the TLS connection options with specified settings.
     *
     * @param Version $minimumVersion Specifies the minimum TLS version that is acceptable for connections.
     * @param string $peerName Specifies the expected name of the peer, used in verifying the peer's certificate.
     * @param bool $peerVerification Indicates whether the peer's SSL certificate should be verified.
     * @param int<0, max> $verificationDepth Specifies the maximum depth for certificate chain verification.
     * @param null|non-empty-list<array{HashingAlgorithm, string}> $peerFingerprints Optional. Specifies peer fingerprints for certificate verification, allowing
     *                                                                               for additional security checks based on expected certificate fingerprints.
     * @param null|non-empty-string $ciphers Specifies the cipher suite(s) to be used for the TLS connection, determining
     *                                       the encryption algorithms that will be available during the TLS handshake.
     * @param null|non-empty-string $certificateAuthorityFile Optional. Specifies the path to a Certificate Authority (CA) file
     *                                                        to be used for verifying the peer's certificate.
     * @param null|non-empty-string $certificateAuthorityPath Optional. Specifies the path to a directory containing Certificate
     *                                                        Authority (CA) certificates, which will be used for verifying the peer's certificate.
     * @param bool $capturePeerCertificate Indicates whether the peer's certificate should be captured during
     *                                     the handshake process. This can be useful for inspection or logging purposes.
     * @param bool $SNIEnabled Indicates whether Server Name Indication (SNI) should be used,
     *                         which allows multiple domains to be served over HTTPS from the same IP address.
     * @param SecurityLevel $securityLevel Specifies the security level for the TLS connection, influencing
     *                                     the choice of cryptographic algorithms.
     * @param null|Certificate $certificate Optional. Specifies a client certificate to be used for the TLS connection,
     *                                      which may be required by servers expecting client authentication.
     * @param list<non-empty-string> $alpnProtocols Specifies the protocols to be used for Application Layer Protocol Negotiation (ALPN),
     *                                              enabling the selection of application-specific protocols within the TLS layer.
     *
     * @pure
     */
    private function __construct(
        public readonly Version       $minimumVersion,
        public readonly string        $peerName,
        public readonly bool          $peerVerification,
        public readonly int           $verificationDepth,
        public readonly ?array        $peerFingerprints,
        public readonly ?string       $ciphers,
        public readonly ?string       $certificateAuthorityFile,
        public readonly ?string       $certificateAuthorityPath,
        public readonly bool          $capturePeerCertificate,
        public readonly bool          $SNIEnabled,
        public readonly SecurityLevel $securityLevel,
        public readonly ?Certificate  $certificate,
        public readonly array         $alpnProtocols,
    ) {
    }

    /**
     * Creates a new instance of ConnectOptions with default settings.
     *
     * @return ClientOptions The new instance with default values.
     *
     * @pure
     */
    public static function create(): self
    {
        return new self(
            minimumVersion: Version::default(),
            peerName: '',
            peerVerification: true,
            verificationDepth: 10,
            peerFingerprints: null,
            ciphers: null,
            certificateAuthorityFile: null,
            certificateAuthorityPath: null,
            capturePeerCertificate: false,
            SNIEnabled: true,
            securityLevel: SecurityLevel::default(),
            certificate: null,
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
     * Specifies the minimum version of the TLS protocol that is acceptable.
     *
     * @param Version $version The minimum TLS version.
     *
     * @return ClientOptions A new instance with the specified minimum TLS version.
     *
     * @mutation-free
     */
    public function withMinimumVersion(Version $version): self
    {
        return new self(
            $version,
            $this->peerName,
            $this->peerVerification,
            $this->verificationDepth,
            $this->peerFingerprints,
            $this->ciphers,
            $this->certificateAuthorityFile,
            $this->certificateAuthorityPath,
            $this->capturePeerCertificate,
            $this->SNIEnabled,
            $this->securityLevel,
            $this->certificate,
            $this->alpnProtocols
        );
    }

    /**
     * Sets the expected name of the peer for certificate verification.
     *
     * @param string $peer_name The expected name of the peer.
     *
     * @return ClientOptions A new instance with the specified peer name.
     *
     * @mutation-free
     */
    public function withPeerName(string $peer_name): self
    {
        return new self(
            $this->minimumVersion,
            $peer_name,
            $this->peerVerification,
            $this->verificationDepth,
            $this->peerFingerprints,
            $this->ciphers,
            $this->certificateAuthorityFile,
            $this->certificateAuthorityPath,
            $this->capturePeerCertificate,
            $this->SNIEnabled,
            $this->securityLevel,
            $this->certificate,
            $this->alpnProtocols
        );
    }

    /**
     * Enables verification of the peer's SSL certificate.
     *
     * @return ClientOptions A new instance with peer verification enabled.
     *
     * @mutation-free
     */
    public function withPeerVerification(bool $peer_verification = true): self
    {
        return new self(
            $this->minimumVersion,
            $this->peerName,
            $peer_verification,
            $this->verificationDepth,
            $this->peerFingerprints,
            $this->ciphers,
            $this->certificateAuthorityFile,
            $this->certificateAuthorityPath,
            $this->capturePeerCertificate,
            $this->SNIEnabled,
            $this->securityLevel,
            $this->certificate,
            $this->alpnProtocols
        );
    }

    /**
     * Sets the maximum depth for certificate chain verification.
     *
     * @param int<0, max> $verification_depth The maximum verification depth.
     *
     * @return ClientOptions A new instance with the specified verification depth.
     *
     * @mutation-free
     */
    public function withVerificationDepth(int $verification_depth): self
    {
        return new self(
            $this->minimumVersion,
            $this->peerName,
            $this->peerVerification,
            $verification_depth,
            $this->peerFingerprints,
            $this->ciphers,
            $this->certificateAuthorityFile,
            $this->certificateAuthorityPath,
            $this->capturePeerCertificate,
            $this->SNIEnabled,
            $this->securityLevel,
            $this->certificate,
            $this->alpnProtocols
        );
    }


    /**
     * Adds a peer fingerprint for certificate verification.
     *
     * @param HashingAlgorithm $hashing_algorithm The hashing algorithm used for the fingerprint.
     * @param string $fingerprint The fingerprint string.
     *
     * @return ClientOptions A new instance with the added peer fingerprint.
     *
     * @mutation-free
     */
    public function withPeerFingerprint(HashingAlgorithm $hashing_algorithm, string $fingerprint): self
    {
        return $this->withPeerFingerprints([
            [$hashing_algorithm, $fingerprint],
        ]);
    }

    /**
     * Sets multiple peer fingerprints for certificate verification.
     *
     * @param null|non-empty-list<array{HashingAlgorithm, string}> $peer_fingerprints An array of peer fingerprints.
     *
     * @return ClientOptions A new instance with the specified peer fingerprints.
     *
     * @mutation-free
     */
    public function withPeerFingerprints(?array $peer_fingerprints): self
    {
        if (null !== $peer_fingerprints) {
            foreach ($peer_fingerprints as [$algorithm, $fingerprint]) {
                Psl\invariant(
                    Byte\length($fingerprint) === $algorithm->getExpectedLength(),
                    'Fingerprint length does not match expected length for "%s" algorithm.',
                    $algorithm->value,
                );
            }
        }

        return new self(
            $this->minimumVersion,
            $this->peerName,
            $this->peerVerification,
            $this->verificationDepth,
            $peer_fingerprints,
            $this->ciphers,
            $this->certificateAuthorityFile,
            $this->certificateAuthorityPath,
            $this->capturePeerCertificate,
            $this->SNIEnabled,
            $this->securityLevel,
            $this->certificate,
            $this->alpnProtocols
        );
    }

    /**
     * Removes all peer fingerprints from the certificate verification process.
     *
     * @return ClientOptions A new instance without any peer fingerprints.
     *
     * @mutation-free
     */
    public function withoutPeerFingerprints(): self
    {
        return $this->withPeerFingerprints(null);
    }

    /**
     * Specifies the cipher suite to be used for the TLS connection.
     *
     * @param non-empty-string $ciphers The cipher suite.
     *
     * @return ClientOptions A new instance with the specified ciphers.
     *
     * @mutation-free
     */
    public function withCiphers(string $ciphers): self
    {
        return new self(
            $this->minimumVersion,
            $this->peerName,
            $this->peerVerification,
            $this->verificationDepth,
            $this->peerFingerprints,
            $ciphers,
            $this->certificateAuthorityFile,
            $this->certificateAuthorityPath,
            $this->capturePeerCertificate,
            $this->SNIEnabled,
            $this->securityLevel,
            $this->certificate,
            $this->alpnProtocols
        );
    }

    /**
     * Sets the path to the Certificate Authority (CA) file for verifying the peer certificate.
     *
     * @param null|non-empty-string $certificate_authority_file The path to the CA file.
     *
     * @return ClientOptions A new instance with the specified CA file path.
     *
     * @mutation-free
     */
    public function withCertificateAuthorityFile(?string $certificate_authority_file): self
    {
        return new self(
            $this->minimumVersion,
            $this->peerName,
            $this->peerVerification,
            $this->verificationDepth,
            $this->peerFingerprints,
            $this->ciphers,
            $certificate_authority_file,
            $this->certificateAuthorityPath,
            $this->capturePeerCertificate,
            $this->SNIEnabled,
            $this->securityLevel,
            $this->certificate,
            $this->alpnProtocols
        );
    }

    /**
     * Sets the path to the Certificate Authority (CA) directory for verifying the peer certificate.
     *
     * @param null|non-empty-string $certificate_authority_path The path to the CA directory.
     *
     * @return ClientOptions A new instance with the specified CA directory path.
     *
     * @mutation-free
     */
    public function withCertificateAuthorityPath(?string $certificate_authority_path): self
    {
        return new self(
            $this->minimumVersion,
            $this->peerName,
            $this->peerVerification,
            $this->verificationDepth,
            $this->peerFingerprints,
            $this->ciphers,
            $this->certificateAuthorityFile,
            $certificate_authority_path,
            $this->capturePeerCertificate,
            $this->SNIEnabled,
            $this->securityLevel,
            $this->certificate,
            $this->alpnProtocols
        );
    }

    /**
     * Enables or disables capturing of the peer's certificate.
     *
     * @param bool $capture_peer_certificate Whether to capture the peer's certificate.
     *
     * @return ClientOptions A new instance with the specified peer certificate capturing setting.
     *
     * @mutation-free
     */
    public function withCapturePeerCertificate(bool $capture_peer_certificate): self
    {
        return new self(
            $this->minimumVersion,
            $this->peerName,
            $this->peerVerification,
            $this->verificationDepth,
            $this->peerFingerprints,
            $this->ciphers,
            $this->certificateAuthorityFile,
            $this->certificateAuthorityPath,
            $capture_peer_certificate,
            $this->SNIEnabled,
            $this->securityLevel,
            $this->certificate,
            $this->alpnProtocols
        );
    }

    /**
     * Enables or disables Server Name Indication (SNI).
     *
     * @param bool $sni_enabled Whether SNI is enabled.
     *
     * @return ClientOptions A new instance with the specified SNI setting.
     *
     * @mutation-free
     */
    public function withSNIEnabled(bool $sni_enabled = true): self
    {
        return new self(
            $this->minimumVersion,
            $this->peerName,
            $this->peerVerification,
            $this->verificationDepth,
            $this->peerFingerprints,
            $this->ciphers,
            $this->certificateAuthorityFile,
            $this->certificateAuthorityPath,
            $this->capturePeerCertificate,
            $sni_enabled,
            $this->securityLevel,
            $this->certificate,
            $this->alpnProtocols
        );
    }

    /**
     * Sets the security level for the TLS connection.
     *
     * @param SecurityLevel $security_level The security level.
     *
     * @return ClientOptions A new instance with the specified security level.
     *
     * @mutation-free
     */
    public function withSecurityLevel(SecurityLevel $security_level): self
    {
        return new self(
            $this->minimumVersion,
            $this->peerName,
            $this->peerVerification,
            $this->verificationDepth,
            $this->peerFingerprints,
            $this->ciphers,
            $this->certificateAuthorityFile,
            $this->certificateAuthorityPath,
            $this->capturePeerCertificate,
            $this->SNIEnabled,
            $security_level,
            $this->certificate,
            $this->alpnProtocols
        );
    }

    /**
     * Specifies a Certificate to be used for the TLS connection.
     *
     * @param null|Certificate $certificate The certificate.
     *
     * @return ClientOptions A new instance with the specified certificate.
     *
     * @mutation-free
     */
    public function withCertificate(?Certificate $certificate): self
    {
        return new self(
            $this->minimumVersion,
            $this->peerName,
            $this->peerVerification,
            $this->verificationDepth,
            $this->peerFingerprints,
            $this->ciphers,
            $this->certificateAuthorityFile,
            $this->certificateAuthorityPath,
            $this->capturePeerCertificate,
            $this->SNIEnabled,
            $this->securityLevel,
            $certificate,
            $this->alpnProtocols
        );
    }

    /**
     * Sets the protocols to be used for Application Layer Protocol Negotiation (ALPN).
     *
     * @param list<non-empty-string> $alpn_protocols The ALPN protocols.
     *
     * @return ClientOptions A new instance with the specified ALPN protocols.
     *
     * @mutation-free
     */
    public function withAlpnProtocols(array $alpn_protocols): self
    {
        return new self(
            $this->minimumVersion,
            $this->peerName,
            $this->peerVerification,
            $this->verificationDepth,
            $this->peerFingerprints,
            $this->ciphers,
            $this->certificateAuthorityFile,
            $this->certificateAuthorityPath,
            $this->capturePeerCertificate,
            $this->SNIEnabled,
            $this->securityLevel,
            $this->certificate,
            $alpn_protocols
        );
    }
}
