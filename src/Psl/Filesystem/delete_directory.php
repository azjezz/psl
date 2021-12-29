<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl\Internal;
use Psl\Str;
use Psl\Vec;

use function rmdir;

/**
 * Delete the directory specified by $directory.
 *
 * @param non-empty-string $directory
 *
 * @throws Exception\RuntimeException In case of an error.
 * @throws Exception\NotFoundException If $directory is not found.
 * @throws Exception\NotDirectoryException If $directory is not a directory.
 * @throws Exception\NotReadableException If $recursive is true, and $directory is not readable.
 */
function delete_directory(string $directory, bool $recursive = false): void
{
    if ($recursive && !namespace\is_symbolic_link($directory)) {
        $nodes = namespace\read_directory($directory);
        [$symbolic_links, $nodes] = Vec\partition(
            $nodes,
            /**
             * @param non-empty-string $node
             */
            static fn(string $node): bool => namespace\is_symbolic_link($node),
        );

        foreach ($symbolic_links as $symbolic_link) {
            namespace\delete_file($symbolic_link);
        }

        foreach ($nodes as $node) {
            if (!namespace\is_directory($node)) {
                namespace\delete_file($node);
            } else {
                namespace\delete_directory($node, true);
            }
        }
    } else {
        // we don't need to check for the directory otherwise, as `read_directory` does so.
        if (!namespace\exists($directory)) {
            throw Exception\NotFoundException::forDirectory($directory);
        }

        if (!namespace\is_directory($directory)) {
            throw Exception\NotDirectoryException::for($directory);
        }
    }

    [$result, $error_message] = Internal\box(static fn() => rmdir($directory));
    // @codeCoverageIgnoreStart
    if (false === $result && namespace\is_directory($directory)) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to delete directory "%s": %s.',
            $directory,
            $error_message ?? 'internal error'
        ));
    }
    // @codeCoverageIgnoreEnd
}
