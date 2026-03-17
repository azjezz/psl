<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function error_clear_last;
use function error_get_last;
use function fileperms;
use function sprintf;

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

    error_clear_last();
    $result = @fileperms($node);

    // @codeCoverageIgnoreStart
    if (false === $result) {
        $error = error_get_last();

        throw new Exception\RuntimeException(sprintf(
            'Failed to retrieve permissions of file "%s": %s',
            $node,
            $error['message'] ?? 'internal error',
        ));
    }

    // @codeCoverageIgnoreEnd

    return $result;
}
