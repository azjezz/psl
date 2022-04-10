<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;
use Psl\Str;

use function fileatime;

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

    [$result, $message] = Psl\Internal\box(
        /**
         * @return false|int
         */
        static fn() => fileatime($node)
    );

    // @codeCoverageIgnoreStart
    if (false === $result) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to retrieve the access time of "%s": %s',
            $node,
            $message ?? 'internal error'
        ));
    }
    // @codeCoverageIgnoreEnd

    return $result;
}
