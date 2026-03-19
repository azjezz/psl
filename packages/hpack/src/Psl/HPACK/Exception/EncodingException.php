<?php

declare(strict_types=1);

namespace Psl\HPACK\Exception;

/**
 * Exception thrown when HPACK encoding fails.
 */
final class EncodingException extends RuntimeException
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
     * Create an exception for a dynamic table size that exceeds the allowed maximum.
     *
     * @param int $size The requested table size.
     * @param int $max The maximum allowed table size.
     *
     * @return self
     */
    public static function forTableSizeExceeded(int $size, int $max): self
    {
        return new self('Dynamic table size ' . $size . ' exceeds maximum of ' . $max . '.');
    }
}
