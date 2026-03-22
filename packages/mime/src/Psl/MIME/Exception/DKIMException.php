<?php

declare(strict_types=1);

namespace Psl\MIME\Exception;

use Psl\MIME\DKIM\Signer;

/**
 * Thrown when a DKIM signing operation fails.
 *
 * Covers RSA/Ed25519 key loading failures, key size violations per RFC 8301,
 * and signature computation failures.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc6376
 * @link https://datatracker.ietf.org/doc/html/rfc8301
 * @link https://datatracker.ietf.org/doc/html/rfc8463
 *
 * @see Signer
 *
 * @api
 */
final class DKIMException extends RuntimeException
{
    /**
     * Create an exception for a private key that cannot be loaded.
     */
    public static function forInvalidKey(string $reason): self
    {
        return new self('DKIM signing failed: ' . $reason);
    }

    /**
     * Create an exception for a signature computation failure.
     */
    public static function forSigningFailure(string $reason): self
    {
        return new self('DKIM signing failed: ' . $reason);
    }
}
