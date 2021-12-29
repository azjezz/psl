<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;
use Psl\Str;

use function fileperms;

/**
 * Get the permissions of $node.
 *
 * @param non-empty-string $node
 *
 * @throws Exception\NotFoundException If $node is not found.
 * @throws Exception\RuntimeException In case of an error.
 */
function get_permissions(string $node): int
{
    if (!namespace\exists($node)) {
        throw Exception\NotFoundException::forNode($node);
    }

    [$result, $message] = Psl\Internal\box(static fn(): int|false => fileperms($node));
    // @codeCoverageIgnoreStart
    if (false === $result) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to retrieve permissions of file "%s": %s',
            $node,
            $message ?? 'internal error'
        ));
    }
    // @codeCoverageIgnoreEnd

    return $result;
}
