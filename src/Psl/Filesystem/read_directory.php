<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use FilesystemIterator;
use Psl;
use Psl\Vec;

/**
 * Return a vec of files and directories inside the specified directory.
 *
 * @return list<string>
 *
 * @throws Psl\Exception\InvariantViolationException If the directory specified by
 *  $directory does not exist, or is not readable.
 */
function read_directory(string $directory): array
{
    Psl\invariant(exists($directory), '$directory does not exists.');
    Psl\invariant(is_directory($directory), '$directory is not a directory.');
    Psl\invariant(is_readable($directory), '$directory is not readable.');

    /** @var list<string> */
    return Vec\values(new FilesystemIterator(
        $directory,
        FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS
    ));
}
