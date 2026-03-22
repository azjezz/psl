<?php

declare(strict_types=1);

namespace Psl\Message\Exception;

/**
 * Thrown when an RFC 5322 structure cannot be parsed from its string representation.
 *
 * Each factory method corresponds to a specific parsing failure (invalid mailbox,
 * invalid group, invalid Message-ID, invalid address list, or malformed message).
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5322
 *
 * @api
 */
final class ParsingException extends RuntimeException
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    /**
     * The input is not a valid mailbox (RFC 5322 §3.4).
     */
    public static function forInvalidMailbox(string $input): self
    {
        return new self('Invalid mailbox: "' . $input . '".');
    }

    /**
     * The input is not a valid group (RFC 5322 §3.4).
     */
    public static function forInvalidGroup(string $input): self
    {
        return new self('Invalid group: "' . $input . '".');
    }

    /**
     * The input is not a valid Message-ID (RFC 5322 §3.6.4).
     */
    public static function forInvalidMessageId(string $input): self
    {
        return new self('Invalid Message-ID: "' . $input . '".');
    }

    /**
     * The input is not a valid address list (RFC 5322 §3.4).
     */
    public static function forInvalidAddressList(string $input): self
    {
        return new self('Invalid address list: "' . $input . '".');
    }

    /**
     * The raw message bytes do not conform to RFC 5322 structure.
     */
    public static function forMalformedMessage(string $reason): self
    {
        return new self('Malformed message: ' . $reason . '.');
    }
}
