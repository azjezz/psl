<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;
use Psl\Str;

use function fileperms;

/**
 * Get the permissions of $filename.
 *
 * @throws Psl\Exception\InvariantViolationException If $filename does not exist.
 * @throws Exception\RuntimeException In case of an error.
 */
function get_permissions(string $filename): int
{
    Psl\invariant(exists($filename), 'File "%s" does not exist.', $filename);

    [$result, $message] = Psl\Internal\box(
        /**
         * @return false|int
         */
        static fn() => fileperms($filename)
    );

    // @codeCoverageIgnoreStart
    if (false === $result) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to retrieve permissions of file "%s": %s',
            $filename,
            $message ?? 'internal error'
        ));
    }
    // @codeCoverageIgnoreEnd

    return $result;
}
