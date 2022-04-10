<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;
use Psl\Str;

use function filectime;

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

    [$result, $message] = Psl\Internal\box(static fn(): false|int => filectime($node));

    // @codeCoverageIgnoreStart
    if (false === $result) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to retrieve the change time of "%s": %s',
            $node,
            $message ?? 'internal error'
        ));
    }
    // @codeCoverageIgnoreEnd

    return $result;
}
