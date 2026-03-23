<?php

declare(strict_types=1);

namespace Psl\DNSSEC\Exception;

/**
 * Thrown when the DNSSEC chain of trust validation fails for a zone,
 * indicating the response cannot be trusted.
 *
 * @api
 */
final class BrokenTrustChainException extends RuntimeException
{
    /**
     * The name of the failure reason that caused the validation to fail.
     */
    public readonly string $failure;

    private function __construct(string $message, string $failure)
    {
        $this->failure = $failure;
        parent::__construct($message);
    }

    /**
     * Create an exception for a DNSSEC chain of trust validation failure.
     */
    public static function forValidationFailure(string $failure): self
    {
        return new self('DNSSEC chain of trust validation failed: ' . $failure . '.', $failure);
    }
}
