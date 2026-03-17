<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function readlink;
use function sprintf;

/**
 * Returns the target of a symbolic link.
 *
 * @param non-empty-string $symbolicLink
 *
 * @throws Exception\NotFoundException If $symbolicLink is not found.
 * @throws Exception\NotSymbolicLinkException If $symbolicLink is not a symbolic link.
 * @throws Exception\RuntimeException If unable to retrieve the target of $symbolicLink.
 *
 * @return non-empty-string
 */
function read_symbolic_link(string $symbolicLink): string
{
    if (!namespace\exists($symbolicLink)) {
        throw Exception\NotFoundException::forSymbolicLink($symbolicLink);
    }

    if (!namespace\is_symbolic_link($symbolicLink)) {
        throw Exception\NotSymbolicLinkException::for($symbolicLink);
    }

    [$result, $message] = Internal\box(static fn(): false|string => readlink($symbolicLink));

    // @codeCoverageIgnoreStart
    if (false === $result) {
        throw new Exception\RuntimeException(sprintf(
            'Failed to retrieve the target of symbolic link "%s": %s',
            $symbolicLink,
            $message ?? 'internal error',
        ));
    }

    // @codeCoverageIgnoreEnd

    /** @var non-empty-string */
    return $result;
}
