<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl\Internal;
use Psl\Str;

use function mkdir;

/**
 * Create the directory specified by $directory.
 *
 * @param non-empty-string $directory
 *
 * @throws Exception\RuntimeException If unable to create the directory.
 */
function create_directory(string $directory, int $permissions = 0777): void
{
    if (namespace\is_directory($directory)) {
        return;
    }

    [$result, $error_message] = Internal\box(
        static fn() => mkdir($directory, $permissions, true)
    );

    // @codeCoverageIgnoreStart
    if (false === $result && !namespace\is_directory($directory)) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to create directory "%s": %s.',
            $directory,
            $error_message ?? 'internal error'
        ));
    }
    // @codeCoverageIgnoreEnd
}
