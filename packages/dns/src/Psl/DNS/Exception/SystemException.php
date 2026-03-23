<?php

declare(strict_types=1);

namespace Psl\DNS\Exception;

use Throwable;

/**
 * Thrown when loading system DNS configuration fails.
 *
 * @api
 */
final class SystemException extends RuntimeException
{
    /**
     * Create an exception for a failed system DNS configuration command.
     */
    public static function forCommandFailed(string $program, string $detail, null|Throwable $previous = null): self
    {
        return new self('Failed to load system DNS configuration via \'' . $program . '\': ' . $detail, $previous);
    }

    /**
     * Create an exception when no global nameservers are found in the system configuration.
     */
    public static function forNoNameservers(): self
    {
        return new self('System DNS configuration contains no global nameservers.');
    }
}
