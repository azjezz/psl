<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function filemtime;
use function sprintf;

/**
 * Get the last time the content of $node was modified.
 *
 * @param non-empty-string $node
 *
 * @throws Exception\NotFoundException If $node is not found.
 * @throws Exception\RuntimeException In case of an error.
 *
 * @return int The last content modification time as a Unix timestamp.
 */
function get_modification_time(string $node): int
{
    if (!namespace\exists($node)) {
        throw Exception\NotFoundException::forNode($node);
    }

    [$result, $message] = Internal\box(static fn(): false|int => filemtime($node));
    // @codeCoverageIgnoreStart
    if (false === $result) {
        throw new Exception\RuntimeException(sprintf(
            'Failed to retrieve the modification time of "%s": %s',
            $node,
            $message ?? 'internal error',
        ));
    }

    // @codeCoverageIgnoreEnd

    return $result;
}
