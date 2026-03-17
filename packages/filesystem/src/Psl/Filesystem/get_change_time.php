<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function error_clear_last;
use function error_get_last;
use function filectime;
use function sprintf;

/**
 * Get the last time the inode of $node was changed
 * (e.g. permission change or ownership change).
 *
 * @param non-empty-string $node
 *
 * @throws Exception\NotFoundException If $node is not found.
 * @throws Exception\RuntimeException In case of an error.
 *
 * @return int The last inode modification time as a Unix timestamp.
 */
function get_change_time(string $node): int
{
    if (!namespace\exists($node)) {
        throw Exception\NotFoundException::forNode($node);
    }

    error_clear_last();
    $result = @filectime($node);

    // @codeCoverageIgnoreStart
    if (false === $result) {
        $error = error_get_last();

        throw new Exception\RuntimeException(sprintf(
            'Failed to retrieve the change time of "%s": %s',
            $node,
            $error['message'] ?? 'internal error',
        ));
    }

    // @codeCoverageIgnoreEnd

    return $result;
}
