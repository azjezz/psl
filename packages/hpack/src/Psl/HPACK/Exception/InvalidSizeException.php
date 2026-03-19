<?php

declare(strict_types=1);

namespace Psl\HPACK\Exception;

/**
 * Exception thrown when a negative size value is provided for table or header list configuration.
 */
final class InvalidSizeException extends InvalidArgumentException
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
     * Create an exception for a negative maximum table size.
     *
     * @param int $size The invalid negative size value.
     *
     * @return self
     */
    public static function forNegativeTableSize(int $size): self
    {
        return new self('Maximum table size must be non-negative, got ' . $size . '.');
    }

    /**
     * Create an exception for a negative maximum header list size.
     *
     * @param int $size The invalid negative size value.
     *
     * @return self
     */
    public static function forNegativeHeaderListSize(int $size): self
    {
        return new self('Maximum header list size must be non-negative, got ' . $size . '.');
    }
}
