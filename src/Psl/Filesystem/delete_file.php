<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;
use Psl\Internal;
use Psl\Str;

use function unlink;

/**
 * Delete the file specified by $filename.
 *
 * @throws Exception\RuntimeException If unable to delete the file.
 * @throws Psl\Exception\InvariantViolationException If the file specified by
 *                                                   $filename does not exist.
 */
function delete_file(string $filename): void
{
    Psl\invariant(is_file($filename), 'File "%s" does not exist.', $filename);

    [$result, $error_message] = Internal\box(static fn() => unlink($filename));
    // @codeCoverageIgnoreStart
    if (false === $result && is_file($filename)) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to delete file "%s": %s.',
            $filename,
            $error_message ?? 'internal error'
        ));
    }
    // @codeCoverageIgnoreEnd
}
