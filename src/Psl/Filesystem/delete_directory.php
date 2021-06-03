<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;
use Psl\Fun;
use Psl\Internal;
use Psl\Iter;
use Psl\Str;
use Psl\Vec;

use function rmdir;

/**
 * Delete the directory specified by $directory.
 *
 * @throws Exception\RuntimeException If unable to delete the directory.
 * @throws Psl\Exception\InvariantViolationException If the directory specified by
 *                                                   $directory does not exist.
 */
function delete_directory(string $directory, bool $recursive = false): void
{
    Psl\invariant(is_directory($directory), 'Directory "%s" does not exist.', $directory);

    if ($recursive && !is_symbolic_link($directory)) {
        [$symbolic_nodes, $nodes] = Vec\partition(
            read_directory($directory),
            static fn(string $node): bool => is_symbolic_link($node)
        );

        Iter\apply($symbolic_nodes, static fn(string $node) => delete_file($node));
        Iter\apply(
            $nodes,
            Fun\when(
                static fn(string $node) => is_directory($node),
                static fn(string $node) => delete_directory($node, true),
                static fn(string $node) => delete_file($node),
            )
        );
    }

    [$result, $error_message] = Internal\box(static fn() => rmdir($directory));
    // @codeCoverageIgnoreStart
    if (false === $result && is_directory($directory)) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to delete directory "%s": %s.',
            $directory,
            $error_message ?? 'internal error'
        ));
    }
    // @codeCoverageIgnoreEnd
}
