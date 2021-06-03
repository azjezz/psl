<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;
use Psl\Internal;
use Psl\Str;

use function chown;
use function lchown;

/**
 * Change the owner of $filename.
 *
 * @throws Exception\RuntimeException If unable to change the ownership for $filename.
 * @throws Psl\Exception\InvariantViolationException If $filename does not exist.
 */
function change_owner(string $filename, int $user): void
{
    Psl\invariant(exists($filename), 'File "%s" does not exist.', $filename);
    if (is_symbolic_link($filename)) {
        $fun = static fn(): bool => lchown($filename, $user);
    } else {
        $fun = static fn(): bool => chown($filename, $user);
    }

    [$success, $error] = Internal\box($fun);
    if (!$success) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to change owner for file "%s": %s',
            $filename,
            $error ?? 'internal error.',
        ));
    }
}
