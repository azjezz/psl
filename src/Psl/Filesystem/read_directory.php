<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use FilesystemIterator;
use Psl;
use Psl\Vec;

/**
 * Return a vec of files and directories inside the specified directory.
 *
 * @param non-empty-string $directory
 *
 * @throws Psl\Exception\InvariantViolationException If the directory specified by
 *                                                   $directory does not exist, or is not readable.
 *
 * @return list<non-empty-string>
 */
function read_directory(string $directory): array
{
    if (!namespace\is_directory($directory)) {
        Psl\invariant_violation('Directory "%s" is not a directory.', $directory);
    }

    if (!namespace\is_readable($directory)) {
        Psl\invariant_violation('Directory "%s" is not readable.', $directory);
    }

    /** @var list<non-empty-string> */
    return Vec\values(new FilesystemIterator(
        $directory,
        FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS
    ));
}
