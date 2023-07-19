<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl\Env;
use Psl\SecureRandom;
use Psl\Str;

/**
 * Create a temporary file.
 *
 * @param non-empty-string|null $directory The directory where the temporary file will be created.
 *                                         If none specified, `Env\temp_dir()` will be used to retrieve
 *                                         the system default temporary directory.
 * @param non-empty-string|null $prefix The prefix of the generated temporary filename.
 *
 * @throws Exception\RuntimeException If unable to create the file.
 * @throws Exception\NotFoundException If $directory is not found.
 * @throws Exception\NotDirectoryException If $directory is not a directory.
 * @throws Exception\InvalidArgumentException If $prefix contains a directory separator.
 *
 * @return non-empty-string The absolute path to the temporary file.
 */
function create_temporary_file(?string $directory = null, ?string $prefix = null): string
{
    $directory ??= Env\temp_dir();
    if (!namespace\exists($directory)) {
        throw Exception\NotFoundException::forDirectory($directory);
    }

    if (!namespace\is_directory($directory)) {
        throw Exception\NotDirectoryException::for($directory);
    }

    $separator = namespace\SEPARATOR;
    if (null !== $prefix) {
        /** @psalm-suppress MissingThrowsDocblock - $offset is within bounds. */
        if (Str\contains($prefix, $separator)) {
            throw new Exception\InvalidArgumentException(Str\format('$prefix should not contain a directory separator ( "%s" ).', $separator));
        }
    } else {
        $prefix = '';
    }

    try {
        /** @psalm-suppress MissingThrowsDocblock - alphabet is within range */
        $filename = $directory . $separator . $prefix . SecureRandom\string(8);
        // @codeCoverageIgnoreStart
    } catch (SecureRandom\Exception\InsufficientEntropyException $e) {
        throw new Exception\RuntimeException('Unable to gather enough entropy to generate filename.', 0, $e);
    }
    // @codeCoverageIgnoreEnd

    create_file($filename);

    return $filename;
}
