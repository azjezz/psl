<?php

declare(strict_types=1);

namespace Psl\DNS\Exception;

use Throwable;

/**
 * Thrown when a DNS query fails due to a network-level transport error.
 *
 * @api
 */
final class NetworkException extends RuntimeException
{
    private function __construct(string $message, Throwable $previous)
    {
        parent::__construct($message, $previous);
    }

    /**
     * Create an exception for a DNS socket that closed unexpectedly.
     */
    public static function forSocketClosed(Throwable $previous): self
    {
        return new self('DNS socket closed unexpectedly.', $previous);
    }

    /**
     * Create an exception for a failed DNS query over a given transport protocol.
     */
    public static function forQueryFailed(string $protocol, string $detail, Throwable $previous): self
    {
        return new self('DNS ' . $protocol . ' query failed: ' . $detail . '.', $previous);
    }

    /**
     * Create an exception for an invalid DNS server address.
     */
    public static function forInvalidServerAddress(string $detail, Throwable $previous): self
    {
        return new self('Invalid DNS server address: ' . $detail . '.', $previous);
    }
}
