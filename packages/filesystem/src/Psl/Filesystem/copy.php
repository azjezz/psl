<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use function sprintf;

/**
 * Copy a file from $source to $destination and preserve executable permission bits.
 *
 * @param non-empty-string $source
 * @param non-empty-string $destination
 *
 * @throws Exception\RuntimeException If unable to copy $source to $destination.
 * @throws Exception\NotFoundException If $source is not found.
 * @throws Exception\NotReadableException If $source is not readable.
 */
function copy(string $source, string $destination, bool $overwrite = false): void
{
    $destinationExists = namespace\is_file($destination);
    if (!$overwrite && $destinationExists) {
        return;
    }

    if (!namespace\is_file($source)) {
        throw Exception\NotFoundException::forFile($source);
    }

    if (!namespace\is_readable($source)) {
        throw Exception\NotReadableException::forFile($source);
    }

    $result = \copy($source, $destination);
    if (!$result) {
        throw new Exception\RuntimeException(sprintf(
            'Failed to copy source file "%s" to destination "%s".',
            $source,
            $destination,
        ));
    }

    // preserve executable permission bits
    namespace\change_permissions(
        $destination,
        namespace\get_permissions($destination) | (namespace\get_permissions($source) & 0o111),
    );
}
