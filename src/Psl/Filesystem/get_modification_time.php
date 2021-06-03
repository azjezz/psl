<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;
use Psl\Internal;
use Psl\Str;

use function filemtime;

/**
 * Get the last time the content of $filename was modified.
 *
 * @throws Psl\Exception\InvariantViolationException If $filename does not exist.
 * @throws Exception\RuntimeException In case of an error.
 */
function get_modification_time(string $filename): int
{
    Psl\invariant(exists($filename), 'File "%s" does not exist.', $filename);

    [$result, $message] = Internal\box(
        /**
         * @return false|int
         */
        static fn() => filemtime($filename)
    );

    // @codeCoverageIgnoreStart
    if (false === $result) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to retrieve the modification time of "%s": %s',
            $filename,
            $message ?? 'internal error'
        ));
    }
    // @codeCoverageIgnoreEnd

    return $result;
}
