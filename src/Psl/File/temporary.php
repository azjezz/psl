<?php

declare(strict_types=1);

namespace Psl\File;

use Psl;
use Psl\Filesystem;

/**
 * Create a temporary file and open it for read and write.
 *
 * @param non-empty-string|null $directory The directory where the temporary filename will be created.
 *                                         If no specified, `Env\temp_dir()` will be used to retrieve
 *                                         the system default temporary directory.
 * @param non-empty-string|null $prefix The prefix of the generated temporary filename.
 *
 * @throws Filesystem\Exception\RuntimeException If unable to create the file.
 * @throws Filesystem\Exception\RuntimeException If unable to create the file.
 * @throws Filesystem\Exception\NotFoundException If $directory is not found.
 * @throws Filesystem\Exception\NotDirectoryException If $directory is not a directory.
 * @throws Filesystem\Exception\InvalidArgumentException If $prefix contains a directory separator.
 * @throws Psl\Exception\InvariantViolationException If $directory doesn't exist or is not writable.
 * @throws Psl\Exception\InvariantViolationException If $prefix contains a directory separator.
 */
function temporary(?string $directory = null, ?string $prefix = null): ReadWriteHandleInterface
{
    $path = Filesystem\create_temporary_file($directory, $prefix);

    return new ReadWriteHandle($path);
}
