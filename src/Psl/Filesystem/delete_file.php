<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl\Internal;
use Psl\Str;

use function unlink;

/**
 * Delete the file specified by $filename.
 *
 * @param non-empty-string $file
 *
 * @throws Exception\RuntimeException If unable to delete the file.
 * @throws Exception\NotFileException If $file is not a file.
 * @throws Exception\NotFoundException If $file is not found.
 */
function delete_file(string $file): void
{
    if (!namespace\exists($file)) {
        throw Exception\NotFoundException::forFile($file);
    }

    if (!namespace\is_file($file)) {
        throw Exception\NotFileException::for($file);
    }

    [$result, $error_message] = Internal\box(static fn(): bool => unlink($file));
    // @codeCoverageIgnoreStart
    if (false === $result && namespace\is_file($file)) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to delete file "%s": %s.',
            $file,
            $error_message ?? 'internal error'
        ));
    }
    // @codeCoverageIgnoreEnd
}
