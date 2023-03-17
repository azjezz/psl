<?php

declare(strict_types=1);

namespace Psl\Filesystem;

/**
 * Create the directory where the $filename is or will be stored.
 *
 * @param non-empty-string $filename
 *
 * @throws Exception\RuntimeException If unable to create the directory.
 *
 * @return non-empty-string
 */
function create_directory_for_file(string $filename, int $permissions = 0777): string
{
    $directory = namespace\get_directory($filename);
    namespace\create_directory($directory, $permissions);

    return $directory;
}
