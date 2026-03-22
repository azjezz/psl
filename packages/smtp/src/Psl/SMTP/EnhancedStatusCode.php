<?php

declare(strict_types=1);

namespace Psl\SMTP;

use Stringable;

/**
 * An RFC 3463 enhanced mail system status code.
 *
 * The code consists of three fields: class.subject.detail, where the class
 * indicates success/failure severity, the subject categorizes the status,
 * and the detail provides specifics within that category.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc3463
 *
 * @api
 */
final readonly class EnhancedStatusCode implements Stringable
{
    /**
     * @param int<0, 999> $class
     * @param int<0, 999> $subject
     * @param int<0, 999> $detail
     */
    public function __construct(
        public int $class,
        public int $subject,
        public int $detail,
    ) {}

    /**
     * 2.XXX.XXX: Report of a positive delivery action.
     */
    public function isSuccess(): bool
    {
        return $this->class === 2;
    }

    /**
     * 4.XXX.XXX: Message as sent is valid, but persistence of some temporary
     * conditions has caused abandonment or delay.
     */
    public function isPersistentTransientFailure(): bool
    {
        return $this->class === 4;
    }

    /**
     * 5.XXX.XXX: Not likely to be resolved by resending the message in current form.
     */
    public function isPermanentFailure(): bool
    {
        return $this->class === 5;
    }

    public function toString(): string
    {
        return $this->class . '.' . $this->subject . '.' . $this->detail;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
