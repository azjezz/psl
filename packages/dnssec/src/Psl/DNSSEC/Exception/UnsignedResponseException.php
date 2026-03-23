<?php

declare(strict_types=1);

namespace Psl\DNSSEC\Exception;

/**
 * Thrown when a DNS response is expected to be signed but lacks the required
 * RRSIG signatures or NSEC/NSEC3 denial-of-existence proofs.
 *
 * @api
 */
final class UnsignedResponseException extends RuntimeException
{
    /**
     * The domain name for which the unsigned response was received.
     */
    public readonly string $queryName;

    /**
     * @param string $message The error message.
     * @param string $queryName The domain name for which the unsigned response was received.
     */
    private function __construct(string $message, string $queryName)
    {
        $this->queryName = $queryName;
        parent::__construct($message);
    }

    /**
     * Create an exception for an unsigned negative (NXDOMAIN/NODATA) response.
     */
    public static function forNegativeResponse(string $queryName): self
    {
        return new self('Unsigned negative response for \'' . $queryName . '\'.', $queryName);
    }

    /**
     * Create an exception when NSEC/NSEC3 proof records are not signed.
     */
    public static function forProof(string $queryName): self
    {
        return new self('NSEC/NSEC3 proof is not signed for \'' . $queryName . '\'.', $queryName);
    }
}
