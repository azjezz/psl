<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;
use Psl\Str;

use function filectime;

/**
 * Get the last time the inode of $filename
 * was changed ( e.g: permission change, ownership change .. etc ).
 *
 * @param non-empty-string $filename
 *
 * @throws Psl\Exception\InvariantViolationException If $filename does not exist.
 * @throws Exception\RuntimeException In case of an error.
 */
function get_change_time(string $filename): int
{
    if (!namespace\exists($filename)) {
        Psl\invariant_violation('File "%s" does not exist.', $filename);
    }

    [$result, $message] = Psl\Internal\box(
        /**
         * @return false|int
         */
        static fn() => filectime($filename)
    );

    // @codeCoverageIgnoreStart
    if (false === $result) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to retrieve the change time of "%s": %s',
            $filename,
            $message ?? 'internal error'
        ));
    }
    // @codeCoverageIgnoreEnd

    return $result;
}
