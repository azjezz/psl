<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;
use Psl\File;
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
 * @throws Psl\Exception\InvariantViolationException If the file specified by
 *                                                   $file does not exist, or is not readable.
 * @throws Exception\RuntimeException In case of an error.
 */
function read_file(string $file, int $offset = 0, ?int $length = null): string
{
    try {
        $handle = File\open_read_only($file);
        $lock = $handle->lock(File\LockType::SHARED);

        $handle->seek($offset);
        $content = $handle->readAll($length);

        $lock->release();
        $handle->close();

        return $content;
    } catch (File\Exception\ExceptionInterface | IO\Exception\ExceptionInterface $previous) {
        // @codeCoverageIgnoreStart
        throw new Exception\RuntimeException(Str\format(
            'Failed to read file "%s".',
            $file,
        ), 0, $previous);
        // @codeCoverageIgnoreEnd
    }
}
