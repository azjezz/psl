<?php

declare(strict_types=1);

namespace Psl\MIME\Exception;

/**
 * Thrown when a Cryptographic Message Syntax (CMS / PKCS#7) operation fails.
 *
 * This covers certificate validation errors, private-key issues, structurally
 * malformed CMS containers, and trust-chain verification failures.
 *
 * @api
 */
final class CMSException extends RuntimeException
{
    /**
     * Create an exception for an invalid or unparseable X.509 certificate.
     */
    public static function forInvalidCertificate(string $reason): self
    {
        return new self('Invalid certificate: ' . $reason);
    }

    /**
     * Create an exception for an invalid or unparseable private key.
     */
    public static function forInvalidKey(string $reason): self
    {
        return new self('Invalid key: ' . $reason);
    }

    /**
     * Create an exception for a CMS/PKCS#7 container whose ASN.1 structure is invalid or unsupported.
     */
    public static function forMalformedStructure(string $reason): self
    {
        return new self('Malformed CMS structure: ' . $reason);
    }

    /**
     * Create an exception when the signer certificate cannot be verified against any
     * of the provided Certificate Authority certificates.
     */
    public static function forUntrustedCertificate(): self
    {
        return new self('Signer certificate is not trusted by any provided CA certificate.');
    }
}
