<?php

declare(strict_types=1);

namespace Psl\File;

use Psl;
use Psl\File;
use Psl\Filesystem;
use Psl\IO;
use Psl\Str;

use function clearstatcache;

/**
 * Write $content to $file.
 *
 * @param non-empty-string $file
 *
 * @throws Psl\Exception\InvariantViolationException If the file specified by
 *                                                   $file is a directory, or is not writeable.
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
    } catch (IO\Exception\ExceptionInterface | Filesystem\Exception\ExceptionInterface $previous) {
        // @codeCoverageIgnoreStart
        throw new Exception\RuntimeException(Str\format(
            'Failed to write to file "%s".',
            $file,
        ), 0, $previous);
        // @codeCoverageIgnoreEnd
    }
}
