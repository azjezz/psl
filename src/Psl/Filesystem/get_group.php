<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;
use Psl\Str;

use function filegroup;

/**
 * Get the group of $node.
 *
 * @param non-empty-string $node
 *
 * @throws Exception\NotFoundException If $node is not found.
 * @throws Exception\RuntimeException In case of an error.
 */
function get_group(string $node): int
{
    if (!namespace\exists($node)) {
        throw Exception\NotFoundException::forNode($node);
    }

    [$result, $message] = Psl\Internal\box(
        /**
         * @return false|int
         */
        static fn() => filegroup($node)
    );

    if (false === $result) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to retrieve group of file "%s": %s',
            $node,
            $message ?? 'internal error'
        ));
    }

    return $result;
}
