<?php

declare(strict_types=1);

namespace Psl\TLS;

use SensitiveParameter;

/**
 * Represents a TLS certificate configuration.
 *
 * @psalm-immutable
 */
final readonly class Certificate
{
    /**
     * @param non-empty-string $certificateFile Path to the certificate file (PEM format).
     * @param non-empty-string $keyFile Path to the private key file (PEM format).
     * @param ?non-empty-string $passphrase Optional passphrase for the private key.
     *
     * @psalm-mutation-free
     */
    public function __construct(
        public string $certificateFile,
        public string $keyFile,
        #[SensitiveParameter]
        public null|string $passphrase = null,
    ) {}

    /**
     * Creates a new {@see Certificate} instance.
     *
     * @param non-empty-string $certificateFile Path to the certificate file (PEM format).
     * @param non-empty-string $keyFile Path to the private key file (PEM format).
     * @param ?non-empty-string $passphrase Optional passphrase for the private key.
     *
     * @pure
     */
    public static function create(
        string $certificateFile,
        string $keyFile,
        #[SensitiveParameter]
        null|string $passphrase = null,
    ): self {
        return new self($certificateFile, $keyFile, $passphrase);
    }
}
