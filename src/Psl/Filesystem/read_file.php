<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;
use Psl\Internal;
use Psl\Str;

use function file_get_contents;

/**
 * Reads entire file into a string.
 *
 * @param int $offset The offset where the reading starts.
 * @param null|int $length Maximum length of data read. The default is to read
 *                         until end of file is reached.
 *
 * @throws Psl\Exception\InvariantViolationException If the file specified by
 *                                                   $file does not exist, or is not readable.
 * @throws Exception\RuntimeException If an error
 */
function read_file(string $file, int $offset = 0, ?int $length = null): string
{
    Psl\invariant(exists($file), 'File "%s" does not exist.', $file);
    Psl\invariant(is_file($file), 'File "%s" is not a file.', $file);
    Psl\invariant(is_readable($file), 'File "%s" is not readable.', $file);

    if (null === $length) {
        [$content, $error] = Internal\box(
            static fn() => file_get_contents($file, false, null, $offset)
        );
    } else {
        [$content, $error] = Internal\box(
            static fn() => file_get_contents($file, false, null, $offset, $length)
        );
    }

    // @codeCoverageIgnoreStart
    if (false === $content || null !== $error) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to read file "%s": %s.',
            $file,
            $error ?? 'internal error',
        ));
    }
    // @codeCoverageIgnoreEnd

    return $content;
}
