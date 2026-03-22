<?php

declare(strict_types=1);

namespace Psl\MIME\Exception;

use Throwable;

use function implode;
use function openssl_error_string;

/**
 * Thrown when an S/MIME cryptographic operation fails.
 *
 * Each factory method drains the OpenSSL error queue and includes all
 * accumulated error strings in the exception message for diagnostic purposes.
 *
 * @api
 */
final class SMIMEException extends RuntimeException
{
    /**
     * Create an exception for a failed S/MIME signing operation.
     *
     * Typically caused by an invalid or mismatched private key, an expired certificate,
     * or an unsupported digest algorithm.
     */
    public static function forSigningFailure(null|Throwable $previous = null): self
    {
        return new self('S/MIME signing failed: ' . self::openSslErrors(), $previous);
    }

    /**
     * Create an exception for a failed S/MIME signature verification.
     *
     * Thrown when the signature is invalid, the signer certificate is untrusted,
     * or the message has been tampered with.
     */
    public static function forVerificationFailure(null|Throwable $previous = null): self
    {
        return new self('S/MIME verification failed: ' . self::openSslErrors(), $previous);
    }

    /**
     * Create an exception for a failed S/MIME encryption operation.
     *
     * Typically caused by an invalid recipient certificate or an unsupported cipher.
     */
    public static function forEncryptionFailure(null|Throwable $previous = null): self
    {
        return new self('S/MIME encryption failed: ' . self::openSslErrors(), $previous);
    }

    /**
     * Create an exception for a failed S/MIME decryption operation.
     *
     * Thrown when the private key does not match any recipient, or the encrypted
     * content is corrupt.
     */
    public static function forDecryptionFailure(null|Throwable $previous = null): self
    {
        return new self('S/MIME decryption failed: ' . self::openSslErrors(), $previous);
    }

    /**
     * Drain the OpenSSL error queue and return all error strings joined by semicolons.
     *
     * Returns "unknown error" if the queue is empty.
     */
    private static function openSslErrors(): string
    {
        $errors = [];
        while (($error = openssl_error_string()) !== false) {
            $errors[] = $error;
        }

        if ($errors === []) {
            return 'unknown error';
        }

        return implode('; ', $errors);
    }
}
