<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl\Internal;
use Psl\Str;

use function symlink;

/**
 * Create a symbolic link for $source.
 *
 * @param non-empty-string $source The file to create a symbolic link for.
 * @param non-empty-string $destination
 *
 * @throws Exception\RuntimeException If unable to create the symbolic link.
 * @throws Exception\NotFoundException If $source is not found.
 * @throws Exception\NotReadableException If $destination is a non-empty directory, and is non-readable {@see delete_directory()}.
 */
function create_symbolic_link(string $source, string $destination): void
{
    if (!namespace\exists($source)) {
        throw Exception\NotFoundException::forNode($source);
    }

    namespace\create_directory_for_file($destination);

    try {
        if (namespace\read_symbolic_link($destination) === $source) {
            return;
        }
    } catch (Exception\NotSymbolicLinkException) {
        try {
            namespace\delete_directory($destination, true);
        } catch (Exception\NotDirectoryException) {
            /** @psalm-suppress MissingThrowsDocblock - $destination is a file. */
            namespace\delete_file($destination);
        }
    } catch (Exception\NotFoundException) {
    }

    [$result, $error_message] = Internal\box(static fn() => symlink($source, $destination));
    // @codeCoverageIgnoreStart
    if (false === $result) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to create symbolic link "%s" from "%s": %s.',
            $destination,
            $source,
            $error_message ?? 'internal error'
        ));
    }
    // @codeCoverageIgnoreEnd
}
