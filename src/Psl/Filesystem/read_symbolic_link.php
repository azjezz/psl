<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl\Internal;
use Psl\Str;

use function readlink;

/**
 * Returns the target of a symbolic link.
 *
 * @param non-empty-string $symbolic_link
 *
 * @throws Exception\NotFoundException If $symbolic_link is not found.
 * @throws Exception\NotSymbolicLinkException If $symbolic_link is not a symbolic link.
 * @throws Exception\RuntimeException If unable to retrieve the target of $symbolic_link.
 *
 * @return non-empty-string
 */
function read_symbolic_link(string $symbolic_link): string
{
    if (!namespace\exists($symbolic_link)) {
        throw Exception\NotFoundException::forSymbolicLink($symbolic_link);
    }

    if (!namespace\is_symbolic_link($symbolic_link)) {
        throw Exception\NotSymbolicLinkException::for($symbolic_link);
    }

    [$result, $message] = Internal\box(
        /**
         * @return false|string
         */
        static fn() => readlink($symbolic_link)
    );

    // @codeCoverageIgnoreStart
    if (false === $result) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to retrieve the target of symbolic link "%s": %s',
            $symbolic_link,
            $message ?? 'internal error'
        ));
    }
    // @codeCoverageIgnoreEnd

    /** @var non-empty-string */
    return $result;
}
