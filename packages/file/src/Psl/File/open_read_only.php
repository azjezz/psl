<?php

declare(strict_types=1);

namespace Psl\File;

/**
 * Open a file handle for read only.
 *
 * @param non-empty-string $file
 *
 * @throws Exception\NotFoundException If $file does not exist.
 * @throws Exception\NotFileException If $file points to a non-file node on the filesystem.
 * @throws Exception\NotReadableException If $file exists, and is non-readable.
 */
function open_read_only(string $file): ReadHandleInterface
{
    return new ReadHandle($file);
}
