<?php

declare(strict_types=1);

namespace Psl\Binary;

/**
 * Interface for binary data readers that operate on a buffered byte string.
 *
 * Extends {@see ReaderInterface} with cursor tracking methods.
 */
interface BufferedReaderInterface extends ReaderInterface
{
    /**
     * Return the current cursor position (number of bytes consumed).
     *
     * @return int<0, max>
     */
    public function cursor(): int;

    /**
     * Return the total number of bytes in the buffer.
     *
     * @return int<0, max>
     */
    public function length(): int;

    /**
     * Return the number of remaining unread bytes.
     *
     * @return int<0, max>
     */
    public function remaining(): int;
}
