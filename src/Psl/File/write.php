<?php

declare(strict_types=1);

namespace Psl\File;

use Psl\File;
use Psl\IO;
use Psl\Str;

use function clearstatcache;

/**
 * Write $content to $file.
 *
 * @param non-empty-string $file
 *
 * @throws Exception\NotFileException If $file points to a non-file node on the filesystem.
 * @throws Exception\AlreadyCreatedException If $file is already created, and $write_mode is {@see WriteMode::MUST_CREATE}.
 * @throws Exception\NotFoundException If $file does not exist, and $write_mode is {@see WriteMode::TRUNCATE} or {@see WriteMode::APPEND}.
 * @throws Exception\NotWritableException If $file exists, and is non-writable.
 * @throws Exception\RuntimeException In case of an error.
 */
function write(string $file, string $content, WriteMode $mode = WriteMode::OPEN_OR_CREATE): void
{
    clearstatcache();

    try {
        $handle = File\open_write_only($file, $mode);
        $lock = $handle->lock(File\LockType::EXCLUSIVE);

        $handle->writeAll($content);

        $lock->release();
        $handle->close();

        clearstatcache();
    } catch (IO\Exception\ExceptionInterface $previous) {
        // @codeCoverageIgnoreStart
        throw new Exception\RuntimeException(Str\format(
            'Failed to write to file "%s".',
            $file,
        ), 0, $previous);
        // @codeCoverageIgnoreEnd
    }
}
