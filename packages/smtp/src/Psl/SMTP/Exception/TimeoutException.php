<?php

declare(strict_types=1);

namespace Psl\SMTP\Exception;

use Throwable;

/**
 * Thrown when an SMTP connection or command times out.
 *
 * @api
 */
final class TimeoutException extends RuntimeException
{
    private function __construct(string $message, null|Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }

    public static function forConnection(string $host, int $port, null|Throwable $previous = null): self
    {
        return new self('Connection to SMTP server \'' . $host . ':' . $port . '\' timed out.', $previous);
    }

    public static function forCommand(string $command, Throwable $previous): self
    {
        return new self('SMTP command \'' . $command . '\' timed out.', $previous);
    }
}
