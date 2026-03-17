<?php

declare(strict_types=1);

namespace Psl\Punycode\Exception;

/**
 * Exception thrown when Punycode encoding or decoding fails.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3492
 */
final class EncodingException extends InvalidArgumentException
{
    /**
     * Create an exception for integer overflow during Punycode encoding/decoding.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3492#section-6.2
     */
    public static function forOverflow(): self
    {
        return new self('Punycode integer overflow during encoding or decoding.');
    }

    /**
     * Create an exception for invalid Punycode input.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3492#section-6.2
     */
    public static function forInvalidInput(string $detail): self
    {
        return new self('Invalid Punycode input: ' . $detail . '.');
    }

    /**
     * Create an exception for a Punycode label that cannot be decoded.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc3492#section-6.2
     */
    public static function forBadEncoding(string $label): self
    {
        return new self('Unable to decode Punycode label "' . $label . '".');
    }
}
