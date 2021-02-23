<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;
use Psl\Internal;
use Psl\Str;

use function symlink;

/**
 * Create a symbolic link for $source.
 *
 * @param string $source The file to create a hard link for.
 *
 * @throws Exception\RuntimeException If unable to create the symbolic link.
 * @throws Psl\Exception\InvariantViolationException If $source does not exist.
 */
function create_symbolic_link(string $source, string $destination): void
{
    Psl\invariant(exists($source), '$source does not exist.');
    $destination_directory = get_directory($destination);
    if (!is_directory($destination_directory)) {
        create_directory($destination_directory);
    }

    if (exists($destination)) {
        if (is_symbolic_link($destination) && read_symbolic_link($destination) === $source) {
            // already exists.
            return;
        }

        if (is_directory($destination)) {
            delete_directory($destination, true);
        } else {
            delete_file($destination);
        }
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
