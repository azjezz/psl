<?php

declare(strict_types=1);

namespace Psl\File;

use Psl\IO;
use Psl\Str;

/**
 * Reads entire file into a string.
 *
 * @param non-empty-string $file
 * @param int<0, max> $offset The offset where the reading starts.
 * @param positive-int|null $length Maximum length of data read.
 *                                  The default is to read until end of file is reached.
 *
 * @throws Exception\NotFoundException If $file does not exist.
 * @throws Exception\NotFileException If $file points to a non-file node on the filesystem.
 * @throws Exception\NotReadableException If $file exists, and is non-readable.
 * @throws Exception\RuntimeException In case of an error.
 */
function read(string $file, int $offset = 0, ?int $length = null): string
{
    try {
        $handle = namespace\open_read_only($file);
        $lock = $handle->lock(namespace\LockType::SHARED);

        $handle->seek($offset);
        $content = $handle->readAll($length);

        $lock->release();
        $handle->close();

        return $content;
    } catch (IO\Exception\ExceptionInterface $previous) {
        // @codeCoverageIgnoreStart
        throw new Exception\RuntimeException(Str\format(
            'Failed to read file "%s".',
            $file,
        ), 0, $previous);
        // @codeCoverageIgnoreEnd
    }
}
