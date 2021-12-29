<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl\Internal;
use Psl\Str;

use function chgrp;
use function lchgrp;

/**
 * Change the group ownership of $node.
 *
 * @param non-empty-string $node
 *
 * @throws Exception\RuntimeException If unable to change the group ownership for $node.
 * @throws Exception\NotFoundException If $node does not exist.
 */
function change_group(string $node, int $group): void
{
    if (!namespace\exists($node)) {
        throw Exception\NotFoundException::forNode($node);
    }

    if (namespace\is_symbolic_link($node)) {
        $fun = static fn(): bool => lchgrp($node, $group);
    } else {
        $fun = static fn(): bool => chgrp($node, $group);
    }

    [$success, $error] = Internal\box($fun);
    if (!$success) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to change the group for node "%s": %s',
            $node,
            $error ?? 'internal error.',
        ));
    }
}
