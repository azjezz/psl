<?php

declare(strict_types=1);

namespace Psl\SMTP;

use Stringable;

/**
 * An SMTP command per RFC 5321 SS4.1.
 *
 * Every SMTP command consists of a verb (the first token) and an optional
 * argument (everything after the first space).
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5321#section-4.1
 *
 * @api
 */
final readonly class Command implements Stringable
{
    /**
     * @param non-empty-string $verb The command verb (e.g. "EHLO", "MAIL", "RCPT", "DATA").
     * @param string $argument The command argument, empty if none.
     */
    public function __construct(
        public string $verb,
        public string $argument = '',
    ) {}

    /**
     * Serialize to the wire format (without trailing CRLF).
     */
    public function toString(): string
    {
        if ($this->argument === '') {
            return $this->verb;
        }

        return $this->verb . ' ' . $this->argument;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
