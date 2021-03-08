<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;
use Psl\Str;

use function filegroup;

/**
 * Get the group of $filename.
 *
 * @throws Psl\Exception\InvariantViolationException If $filename does not exist.
 * @throws Exception\RuntimeException In case of an error.
 */
function get_group(string $filename): int
{
    Psl\invariant(exists($filename), '$filename does not exists.');

    [$result, $message] = Psl\Internal\box(
        /**
         * @return false|int
         */
        static fn() => filegroup($filename)
    );

    if (false === $result) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to retrieve group of file "%s": %s',
            $filename,
            $message ?? 'internal error'
        ));
    }

    return $result;
}
