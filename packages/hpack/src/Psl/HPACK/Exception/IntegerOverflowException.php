<?php

declare(strict_types=1);

namespace Psl\HPACK\Exception;

/**
 * Exception thrown when an HPACK-encoded integer value exceeds PHP_INT_MAX.
 */
final class IntegerOverflowException extends OverflowException
{
    /**
     * Private constructor; use named factory methods instead.
     *
     * @param string $message The exception message.
     */
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    /**
     * Create an exception for an integer value that overflows PHP_INT_MAX.
     *
     * @return self
     */
    public static function forValue(): self
    {
        return new self('HPACK integer value exceeds PHP_INT_MAX.');
    }
}
