<?php

declare(strict_types=1);

namespace Psl\TCP\TLS;

/**
 * Represents a TLS certificate for secure TCP connections.
 *
 * This class encapsulates the necessary information for a TLS certificate, including
 * the certificate file itself, an optional private key file, and an optional passphrase
 * for the key.
 *
 * @immutable
 */
final class Certificate
{
    /**
     * Constructor for the Certificate class.
     *
     * @param non-empty-string $certificateFile The path to the certificate file.
     * @param non-empty-string|null $keyFile The path to the private key file associated with the certificate, if any.
     * @param non-empty-string|null $passphrase The passphrase for the private key file, if the file is encrypted.
     *
     * @pure
     */
    public function __construct(
        public readonly string  $certificateFile,
        public readonly ?string $keyFile = null,
        public readonly ?string $passphrase = null,
    ) {
    }

    /**
     * Creates a new Certificate instance.
     *
     * @param non-empty-string $certificate_file The path to the certificate file.
     * @param non-empty-string|null $key_file The path to the private key file associated with the certificate.
     * @param non-empty-string|null $passphrase The passphrase for the private key file, if the file is encrypted.
     *
     * @pure
     */
    public static function create(string $certificate_file, ?string $key_file = null, ?string $passphrase = null): Certificate
    {
        return new self($certificate_file, $key_file, $passphrase);
    }
}
