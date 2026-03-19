<?php

declare(strict_types=1);

namespace Psl\HPACK\Exception;

/**
 * Exception thrown when an invalid HPACK table index is referenced.
 */
final class InvalidTableIndexException extends InvalidArgumentException
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
     * Create an exception for a zero table index, which is not valid in HPACK.
     *
     * @return self
     */
    public static function forZeroIndex(): self
    {
        return new self('HPACK table index 0 is not valid.');
    }

    /**
     * Create an exception for a table index that exceeds the valid range.
     *
     * @param int $index The invalid index value.
     * @param int $maxIndex The maximum valid index.
     *
     * @return self
     */
    public static function forOutOfRange(int $index, int $maxIndex): self
    {
        return new self('HPACK table index ' . $index . ' is out of range (max: ' . $maxIndex . ').');
    }
}
