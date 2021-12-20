<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;
use Psl\File;
use Psl\IO;
use Psl\Str;

use function clearstatcache;

/**
 * Append $content to $file.
 *
 * If $file does not exist, it will be created.
 *
 * @param non-empty-string $file
 * @param non-empty-string $content
 *
 * @throws Psl\Exception\InvariantViolationException If the file specified by
 *                                                   $file is a directory, or is not writeable.
 * @throws Exception\RuntimeException In case of an error.
 */
function append_file(string $file, string $content): void
{
    clearstatcache();

    try {
        $handle = File\open_write_only($file, File\WriteMode::APPEND);
        $lock = $handle->lock(File\LockType::EXCLUSIVE);

        $handle->writeAll($content);

        $lock->release();
        $handle->close();

        clearstatcache();
    } catch (File\Exception\ExceptionInterface | IO\Exception\ExceptionInterface $previous) {
        // @codeCoverageIgnoreStart
        throw new Exception\RuntimeException(Str\format(
            'Failed to write to file "%s".',
            $file,
        ), 0, $previous);
        // @codeCoverageIgnoreEnd
    }
}
