<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;
use Psl\Internal;
use Psl\Str;

/**
 * Ge the size of $filename.
 *
 * @param non-empty-string $filename
 *
 * @throws Psl\Exception\InvariantViolationException If $filename does not exist or is not readable.
 * @throws Exception\RuntimeException In case of an error.
 */
function file_size(string $filename): int
{
    if (!namespace\is_file($filename) || !namespace\is_readable($filename)) {
        Psl\invariant_violation('File "%s" does not exist, or is not readable.', $filename);
    }

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
