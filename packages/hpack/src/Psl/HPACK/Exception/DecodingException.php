<?php

declare(strict_types=1);

namespace Psl\HPACK\Exception;

/**
 * Exception thrown when HPACK decoding fails due to malformed or invalid input.
 */
final class DecodingException extends RuntimeException
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
     * Create an exception for Huffman padding that contains non-1 bits.
     *
     * @return self
     */
    public static function forInvalidHuffmanPadding(): self
    {
        return new self('Invalid Huffman padding: padding contains non-1 bits.');
    }

    /**
     * Create an exception for an EOS symbol encountered in Huffman-encoded data.
     *
     * @return self
     */
    public static function forEosInHuffmanData(): self
    {
        return new self('EOS symbol found in Huffman-encoded data.');
    }

    /**
     * Create an exception for an incomplete Huffman sequence at the end of data.
     *
     * @return self
     */
    public static function forIncompleteHuffmanSequence(): self
    {
        return new self('Incomplete Huffman sequence at end of data.');
    }

    /**
     * Create an exception for a table size update that does not occur at the start of a header block.
     *
     * @return self
     */
    public static function forTableSizeUpdateNotAtBlockStart(): self
    {
        return new self('Dynamic table size update must occur at the start of a header block.');
    }

    /**
     * Create an exception for unexpected end of HPACK data.
     *
     * @return self
     */
    public static function forUnexpectedEndOfData(): self
    {
        return new self('Unexpected end of HPACK data.');
    }

    /**
     * Create an exception for a string length that exceeds available data.
     *
     * @return self
     */
    public static function forInvalidStringLength(): self
    {
        return new self('String length exceeds available data.');
    }

    /**
     * Create an exception for a dynamic table size update that exceeds the protocol limit.
     *
     * @return self
     */
    public static function forTableSizeExceedsLimit(): self
    {
        return new self('Dynamic table size update exceeds the protocol limit.');
    }

    /**
     * Create an exception for too many dynamic table size updates at the start of a header block.
     *
     * @return self
     */
    public static function forTooManyTableSizeUpdates(): self
    {
        return new self('Too many dynamic table size updates at start of header block.');
    }
}
