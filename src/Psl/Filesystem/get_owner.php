<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;
use Psl\Str;

use function fileowner;

/**
 * Get the owner of $filename.
 *
 * @throws Psl\Exception\InvariantViolationException If $filename does not exist.
 * @throws Exception\RuntimeException In case of an error.
 */
function get_owner(string $filename): int
{
    Psl\invariant(exists($filename), 'File "%s" does not exist.', $filename);

    [$result, $message] = Psl\Internal\box(
        /**
         * @return false|int
         */
        static fn() => fileowner($filename)
    );

    if (false === $result) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to retrieve owner of file "%s": %s',
            $filename,
            $message ?? 'internal error'
        ));
    }

    return $result;
}
