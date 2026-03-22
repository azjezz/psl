<?php

declare(strict_types=1);

namespace Psl\SMTP\Exception;

/**
 * Thrown when SMTP authentication fails or the requested mechanism is unsupported.
 *
 * @api
 */
final class AuthenticationException extends RuntimeException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function forRejected(string $mechanism, int $code, string $serverMessage): self
    {
        return new self('SMTP ' . $mechanism . ' authentication rejected: ' . $code . ' ' . $serverMessage . '.');
    }

    public static function forUnsupportedMechanism(string $mechanism): self
    {
        return new self('SMTP server does not support the \'' . $mechanism . '\' authentication mechanism.');
    }
}
