<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;
use Psl\Internal;
use Psl\Str;

use function readlink;

/**
 * Returns the target of a symbolic link.
 *
 * @throws Psl\Exception\InvariantViolationException If the link specified by
 *                                                   $symbolic_link does not exist, or is not a symbolic link.
 * @throws Exception\RuntimeException If unable to retrieve the target
 *                                    of $symbolic_link.
 */
function read_symbolic_link(string $symbolic_link): string
{
    Psl\invariant(exists($symbolic_link), 'Symbolic link "%s" does not exist.', $symbolic_link);
    Psl\invariant(is_symbolic_link($symbolic_link), 'Symbolic link "%s" is not a symbolic link.', $symbolic_link);

    [$result, $message] = Internal\box(
        /**
         * @return false|string
         */
        static fn() => readlink($symbolic_link)
    );

    // @codeCoverageIgnoreStart
    if (false === $result) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to retrieve the target of symbolic link "%s": %s',
            $symbolic_link,
            $message ?? 'internal error'
        ));
    }
    // @codeCoverageIgnoreEnd

    return $result;
}
