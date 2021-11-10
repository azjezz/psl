<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;
use Psl\File;
use Psl\IO;
use Psl\Str;

use function clearstatcache;

/**
 * Write $content to $file.
 *
 * If $file does not exist, it will be created.
 *
 * @throws Psl\Exception\InvariantViolationException If the file specified by
 *                                                   $file is a directory, or is not writeable.
 * @throws Exception\RuntimeException In case of an error.
 */
function write_file(string $file, string $content): void
{
    clearstatcache();

    try {
        if (namespace\is_file($file)) {
            $mode = File\WriteMode::TRUNCATE;
        } else {
            $mode = File\WriteMode::OPEN_OR_CREATE;
        }

        $handle = File\open_write_only($file, $mode);
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
