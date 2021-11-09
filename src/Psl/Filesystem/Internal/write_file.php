<?php

declare(strict_types=1);

namespace Psl\Filesystem\Internal;

use Psl;
use Psl\Filesystem;
use Psl\Filesystem\Exception;
use Psl\Internal;
use Psl\Str;

use function clearstatcache;
use function file_put_contents;

use const FILE_APPEND;
use const LOCK_EX;

/**
 * Write $content to $file.
 *
 * @param bool $append If true, and $file exists, append $content to $file instead of overwriting it.
 *
 * @throws Psl\Exception\InvariantViolationException If the file specified by
 *                                                   $file does not exist, or is not writeable.
 * @throws Exception\RuntimeException In case of an error.
 */
function write_file(string $file, string $content, bool $append): void
{
    if (Filesystem\exists($file)) {
        Psl\invariant(Filesystem\is_file($file), 'File "%s" is not a file.', $file);
        Psl\invariant(Filesystem\is_writable($file), 'File "%s" is not writeable.', $file);
    } else {
        Filesystem\create_file($file);
    }

    if ($append) {
        // Mutually exclusive with LOCK_EX since appends are atomic
        // and thus there is no reason to lock.
        $flags = FILE_APPEND;
    } else {
        $flags = LOCK_EX;
    }

    [$written, $error] = Internal\box(
        static fn() => file_put_contents($file, $content, $flags)
    );

    // @codeCoverageIgnoreStart
    if (false === $written || null !== $error) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to write to file "%s": %s.',
            $file,
            $error ?? 'internal error',
        ));
    }

    $length = Str\Byte\length($content);
    if ($written !== $length) {
        throw new Exception\RuntimeException(Str\format(
            'Failed to write the whole content to "%s" ( %g of %g bytes written ).',
            $file,
            $written,
            $length,
        ));
    }
    // @codeCoverageIgnoreEnd

    clearstatcache();
}
