<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;
use Psl\Internal;
use Psl\Str;

/**
 * Ge the size of $filename.
 *
 * @throws Psl\Exception\InvariantViolationException If $filename does not exist or is not readable.
 * @throws Exception\RuntimeException In case of an error.
 */
function file_size(string $filename): int
{
    Psl\invariant(is_file($filename) && is_readable($filename), 'File "%s" does not exist.', $filename);

    // @codeCoverageIgnoreStart
    [$size, $message] = Internal\box(static fn() => filesize($filename));
    if (false === $size) {
        throw new Exception\RuntimeException(Str\format(
            'Error reading the size of file "%s": %s',
            $filename,
            $message ?? 'internal error'
        ));
    }
    // @codeCoverageIgnoreEnd

    return $size;
}
