<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl\Internal;
use Psl\Str;

/**
 * Get the size of $file.
 *
 * @param non-empty-string $file
 *
 * @throws Exception\NotFileException If $file is not a file.
 * @throws Exception\NotReadableException If $file is not readable.
 * @throws Exception\RuntimeException In case of an error.
 * @throws Exception\NotFoundException If $file is not found.
 *
 * @return int<0, max>
 */
function file_size(string $file): int
{
    if (!namespace\exists($file)) {
        throw Exception\NotFoundException::forFile($file);
    }

    if (!namespace\is_file($file)) {
        throw Exception\NotFileException::for($file);
    }

    if (!namespace\is_readable($file)) {
        throw Exception\NotReadableException::forFile($file);
    }

    // @codeCoverageIgnoreStart
    [$size, $message] = Internal\box(static fn() => filesize($file));
    if (false === $size) {
        throw new Exception\RuntimeException(Str\format(
            'Error reading the size of file "%s": %s',
            $file,
            $message ?? 'internal error'
        ));
    }
    // @codeCoverageIgnoreEnd

    /** @var int<0, max> */
    return $size;
}
