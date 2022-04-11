<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl\Internal;
use Psl\Str;

use function chmod;

/**
 * Change permission mode of $node.
 *
 * @param non-empty-string $node
 * @param int $permissions Permissions as an octal number, e.g. `0755`.
 *
 * @throws Exception\RuntimeException If unable to change the mode for the given $node.
 * @throws Exception\NotFoundException If $node does not exist.
 */
function change_permissions(string $node, int $permissions): void
{
    if (!namespace\exists($node)) {
        throw Exception\NotFoundException::forNode($node);
    }

    [$success, $error] = Internal\box(static fn(): bool => chmod($node, $permissions));
    // @codeCoverageIgnoreStart
    if (!$success) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to change permissions for file "%s": %s',
            $node,
            $error ?? 'internal error.',
        ));
    }
    // @codeCoverageIgnoreEnd
}
