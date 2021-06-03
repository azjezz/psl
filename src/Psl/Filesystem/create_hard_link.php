<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;
use Psl\Internal;
use Psl\Str;

use function link;

/**
 * Create a hard link for $source.
 *
 * @param string $source The file to create a hard link for.
 *
 * @throws Psl\Exception\InvariantViolationException If $source is not a file, or does not exist.
 * @throws Exception\RuntimeException If unable to create a hard file.
 */
function create_hard_link(string $source, string $destination): void
{
    Psl\invariant(exists($source), 'Source file "%s" does not exist.', $source);
    Psl\invariant(is_file($source), 'Source "%s" is not a file.', $source);

    $destination_directory = get_directory($destination);
    if (!is_directory($destination_directory)) {
        create_directory($destination_directory);
    }

    if (exists($destination)) {
        if (get_inode($destination) === get_inode($source)) {
            // already exists.
            return;
        }

        if (is_directory($destination)) {
            delete_directory($destination, true);
        } else {
            delete_file($destination);
        }
    }

    [$result, $error_message] = Internal\box(static fn() => link($source, $destination));
    // @codeCoverageIgnoreStart
    if (false === $result) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to create hard link "%s" from "%s": %s.',
            $destination,
            $source,
            $error_message ?? 'internal error'
        ));
    }
    // @codeCoverageIgnoreEnd
}
