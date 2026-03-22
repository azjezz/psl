<?php

declare(strict_types=1);

namespace Psl\SMTP\Exception;

/**
 * Thrown when the server sends a malformed or unexpected SMTP response.
 *
 * @api
 */
final class ProtocolException extends RuntimeException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function forMalformedResponse(string $line): self
    {
        return new self('SMTP server sent malformed response: \'' . $line . '\'.');
    }

    public static function forUnexpectedCode(int $expected, int $actual, string $message): self
    {
        return new self('Expected SMTP response code ' . $expected . ', got ' . $actual . ': ' . $message . '.');
    }
}
