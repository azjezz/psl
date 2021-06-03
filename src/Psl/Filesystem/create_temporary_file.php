<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;
use Psl\Env;
use Psl\SecureRandom;
use Psl\Str;

/**
 * Create a temporary file.
 *
 * @param string|null $directory The directory where the temporary filename will be created.
 *                               If no specified, `Env\temp_dir()` will be used to retrieve
 *                               the system default temporary directory.
 * @param string|null $prefix The prefix of the generated temporary filename.
 *
 * @throws Psl\Exception\InvariantViolationException If $directory doesn't exist or is not writable.
 * @throws Psl\Exception\InvariantViolationException If $prefix contains a directory separator.
 * @throws Exception\RuntimeException If unable to create the file.
 *
 * @return string The absolute path to the temporary file.
 */
function create_temporary_file(?string $directory = null, ?string $prefix = null): string
{
    if (null !== $directory) {
        Psl\invariant(is_directory($directory), 'Directory "%s" is not a directory.', $directory);
        Psl\invariant(is_writable($directory), 'Directory "%s" is not writable.', $directory);
    } else {
        $directory = Env\temp_dir();
    }

    if (null !== $prefix) {
        Psl\invariant(
            !Str\contains($prefix, SEPARATOR),
            '$prefix should not contain a directory separator ( "%s" ).',
            SEPARATOR
        );
    } else {
        $prefix = '';
    }

    try {
        $filename = $directory . '/' . $prefix . SecureRandom\string(8);
        // @codeCoverageIgnoreStart
    } catch (SecureRandom\Exception\InsufficientEntropyException $e) {
        throw new Exception\RuntimeException('Unable to gather enough entropy to generate filename.', 0, $e);
    }
    // @codeCoverageIgnoreEnd

    create_file($filename);

    return $filename;
}
