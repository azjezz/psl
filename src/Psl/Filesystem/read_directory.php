<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use FilesystemIterator;
use Psl\Vec;

/**
 * Return a vec of files and directories inside the specified directory.
 *
 * @param non-empty-string $directory
 *
 * @throws Exception\NotFoundException If $directory is not found.
 * @throws Exception\NotDirectoryException If $directory is not a directory.
 * @throws Exception\NotReadableException If $directory is not readable.
 *
 * @return list<non-empty-string>
 */
function read_directory(string $directory): array
{
    if (!namespace\exists($directory)) {
        throw Exception\NotFoundException::forDirectory($directory);
    }

    if (!namespace\is_directory($directory)) {
        throw Exception\NotDirectoryException::for($directory);
    }

    if (!namespace\is_readable($directory)) {
        throw Exception\NotReadableException::forDirectory($directory);
    }

    /** @var list<non-empty-string> */
    return Vec\values(new FilesystemIterator(
        $directory,
        FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS
    ));
}
