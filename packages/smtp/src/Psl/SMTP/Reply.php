<?php

declare(strict_types=1);

namespace Psl\SMTP;

use Stringable;

use function intdiv;

/**
 * An SMTP reply per RFC 5321, with optional RFC 3463 enhanced status code.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc5321
 * @link https://datatracker.ietf.org/doc/html/rfc3463
 *
 * @api
 */
final readonly class Reply implements Stringable
{
    public function __construct(
        public int $code,
        public null|EnhancedStatusCode $enhancedStatus,
        public string $message,
    ) {}

    /**
     * 2yz: The requested action has been successfully completed.
     */
    public function isPositiveCompletion(): bool
    {
        return intdiv($this->code, 100) === 2;
    }

    /**
     * 3yz: The command has been accepted, but the requested action is held
     * in abeyance, pending receipt of further information.
     */
    public function isPositiveIntermediate(): bool
    {
        return intdiv($this->code, 100) === 3;
    }

    /**
     * 4yz: The command was not accepted, and the requested action did not occur.
     * The error condition is temporary.
     */
    public function isTransientNegativeCompletion(): bool
    {
        return intdiv($this->code, 100) === 4;
    }

    /**
     * 5yz: The command was not accepted and the requested action did not occur.
     * The client SHOULD NOT repeat the exact request.
     */
    public function isPermanentNegativeCompletion(): bool
    {
        return intdiv($this->code, 100) === 5;
    }

    /**
     * x0z: Syntax errors, syntactically correct but unfitting commands, or unimplemented commands.
     */
    public function isSyntaxCategory(): bool
    {
        return intdiv($this->code % 100, 10) === 0;
    }

    /**
     * x1z: Replies to requests for information.
     */
    public function isInformationCategory(): bool
    {
        return intdiv($this->code % 100, 10) === 1;
    }

    /**
     * x2z: Replies referring to the transmission channel.
     */
    public function isConnectionsCategory(): bool
    {
        return intdiv($this->code % 100, 10) === 2;
    }

    /**
     * x3z/x4z: Unspecified.
     */
    public function isUnspecifiedCategory(): bool
    {
        $second = intdiv($this->code % 100, 10);

        return $second === 3 || $second === 4;
    }

    /**
     * x5z: Replies indicating the status of the receiver mail system.
     */
    public function isMailSystemCategory(): bool
    {
        return intdiv($this->code % 100, 10) === 5;
    }

    public function toString(): string
    {
        if ($this->enhancedStatus !== null) {
            return $this->code . ' ' . $this->enhancedStatus->toString() . ' ' . $this->message;
        }

        return $this->code . ' ' . $this->message;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
