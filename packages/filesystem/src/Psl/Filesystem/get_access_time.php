<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function error_clear_last;
use function error_get_last;
use function fileatime;
use function sprintf;

/**
 * Get last access time of $node.
 *
 * @param non-empty-string $node
 *
 * @throws Exception\NotFoundException If $node is not found.
 * @throws Exception\RuntimeException In case of an error.
 */
function get_access_time(string $node): int
{
    if (!namespace\exists($node)) {
        throw Exception\NotFoundException::forNode($node);
    }

    error_clear_last();
    $result = @fileatime($node);

    // @codeCoverageIgnoreStart
    if (false === $result) {
        $error = error_get_last();

        throw new Exception\RuntimeException(sprintf(
            'Failed to retrieve the access time of "%s": %s',
            $node,
            $error['message'] ?? 'internal error',
        ));
    }

    // @codeCoverageIgnoreEnd

    return $result;
}
