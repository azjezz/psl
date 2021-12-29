<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl\Internal;
use Psl\Str;

use function chown;
use function lchown;

/**
 * Change the owner of $node.
 *
 * @param non-empty-string $node
 *
 * @throws Exception\RuntimeException If unable to change the ownership for $node.
 * @throws Exception\NotFoundException If $node does not exist.
 */
function change_owner(string $node, int $user): void
{
    if (!namespace\exists($node)) {
        throw Exception\NotFoundException::forNode($node);
    }

    if (is_symbolic_link($node)) {
        $fun = static fn(): bool => lchown($node, $user);
    } else {
        $fun = static fn(): bool => chown($node, $user);
    }

    [$success, $error] = Internal\box($fun);
    if (!$success) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to change owner for node "%s": %s',
            $node,
            $error ?? 'internal error.',
        ));
    }
}
