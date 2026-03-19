<?php

declare(strict_types=1);

namespace Psl\HPACK\Exception;

/**
 * Exception thrown when the encoded header list size exceeds the configured limit.
 */
final class HeaderListSizeException extends OverflowException
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
     * Create an exception for a header list size that exceeds the allowed limit.
     *
     * @param int $size The actual header list size.
     * @param int $limit The configured maximum limit.
     *
     * @return self
     */
    public static function forExceededLimit(int $size, int $limit): self
    {
        return new self('Header list size ' . $size . ' exceeds limit of ' . $limit . '.');
    }
}
