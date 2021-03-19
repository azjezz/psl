<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\Vec;

final class Writer implements WriteHandleInterface
{
    private WriteHandleInterface $handle;

    public function __construct(WriteHandleInterface $handle)
    {
        $this->handle = $handle;
    }

    public function getHandle(): WriteHandleInterface
    {
        return $this->handle;
    }

    /**
     * An immediate unordered write.
     *
     * @returns int the number of bytes written on success
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the write would block.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     */
    public function write(string $bytes): int
    {
        return $this->handle->write($bytes);
    }

    /**
     * Writes all the specified string values.
     *
     * @returns int the number of bytes written on success
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the write would block.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     *
     * @see WriteHandleInterface::write()
     */
    public function writeAll(string ...$bytes): int
    {
        $result = 0;
        foreach ($bytes as $str) {
            $result += $this->write($str);
        }

        return $result;
    }

    /**
     * Writes the specified string value, followed by the current line terminator.
     *
     * @returns int the number of bytes written on success
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the write would block.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     */
    public function writeLine(string $bytes): int
    {
        return $this->write($bytes . "\n");
    }


    /**
     * Writes all the specified string values, each followed by the current line terminator.
     *
     * @returns int the number of bytes written on success
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\BlockingException If the handle is a socket or similar, and the write would block.
     * @throws Exception\RuntimeException If an error occurred during the operation.
     *
     * @see WriteHandleInterface::write()
     */
    public function writeAllLines(string ...$bytes): int
    {
        return $this->writeAll(...Vec\map($bytes, static fn(string $bytes): string => $bytes . "\n"));
    }

    /**
     * Flush the handle.
     *
     * @throws Exception\AlreadyClosedException If the handle has been already closed.
     * @throws Exception\RuntimeException If unable to flush the handle.
     */
    public function flush(): void
    {
        $this->handle->flush();
    }
}
