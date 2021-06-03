<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use FilesystemIterator;
use Psl;
use Psl\Vec;

/**
 * Return a vec of files and directories inside the specified directory.
 *
 * @throws Psl\Exception\InvariantViolationException If the directory specified by
 *                                                   $directory does not exist, or is not readable.
 *
 * @return list<string>
 */
function read_directory(string $directory): array
{
    Psl\invariant(exists($directory), 'Directory "%s" does not exist.', $directory);
    Psl\invariant(is_directory($directory), 'Directory "%s" is not a directory.', $directory);
    Psl\invariant(is_readable($directory), 'Directory "%s" is not readable.', $directory);

    /** @var list<string> */
    return Vec\values(new FilesystemIterator(
        $directory,
        FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS
    ));
}
