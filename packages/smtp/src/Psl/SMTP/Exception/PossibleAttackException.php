<?php

declare(strict_types=1);

namespace Psl\SMTP\Exception;

/**
 * Thrown when input contains characters that could be used for SMTP command injection.
 *
 * CRLF sequences and null bytes in command verbs or arguments can be exploited
 * to inject additional SMTP commands into the conversation.
 *
 * @api
 */
final class PossibleAttackException extends RuntimeException
{
    public static function forCRLFInjection(): self
    {
        return new self('SMTP command contains CRLF characters, which could be used for command injection.');
    }

    public static function forNullByteInjection(): self
    {
        return new self('SMTP command contains null bytes, which could be used for command injection.');
    }
}
