<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function error_clear_last;
use function error_get_last;
use function fileowner;
use function sprintf;

/**
 * Get the owner of $node.
 *
 * @param non-empty-string $node
 *
 * @throws Exception\NotFoundException If $node is not found.
 * @throws Exception\RuntimeException In case of an error.
 */
function get_owner(string $node): int
{
    if (!namespace\exists($node)) {
        throw Exception\NotFoundException::forNode($node);
    }

    error_clear_last();
    $result = @fileowner($node);

    if (false === $result) {
        $error = error_get_last();

        throw new Exception\RuntimeException(sprintf(
            'Failed to retrieve owner of file "%s": %s',
            $node,
            $error['message'] ?? 'internal error',
        ));
    }

    return $result;
}
