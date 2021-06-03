<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;
use Psl\Internal;
use Psl\Str;

use function chmod;

/**
 * Changes mode permission of $filename.
 *
 * @throws Exception\RuntimeException If unable to change the mode for the given $filename.
 * @throws Psl\Exception\InvariantViolationException If $filename does not exists.
 */
function change_permissions(string $filename, int $permissions): void
{
    Psl\invariant(exists($filename), 'File "%s" does not exist.', $filename);

    [$success, $error] = Internal\box(static fn(): bool => chmod($filename, $permissions));
    // @codeCoverageIgnoreStart
    if (!$success) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to change permissions for file "%s": %s',
            $filename,
            $error ?? 'internal error.',
        ));
    }
    // @codeCoverageIgnoreEnd
}
