<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;
use Psl\Internal;
use Psl\Str;

use function chgrp;
use function lchgrp;

/**
 * Change the group ownership of $filename.
 *
 * @throws Exception\RuntimeException If unable to change the group ownership for $filename.
 * @throws Psl\Exception\InvariantViolationException If $filename does not exist.
 */
function change_group(string $filename, int $group): void
{
    Psl\invariant(exists($filename), 'File "%s" does not exist.', $filename);

    if (is_symbolic_link($filename)) {
        $fun = static fn(): bool => lchgrp($filename, $group);
    } else {
        $fun = static fn(): bool => chgrp($filename, $group);
    }

    [$success, $error] = Internal\box($fun);
    if (!$success) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to change the group for file "%s": %s',
            $filename,
            $error ?? 'internal error.',
        ));
    }
}
