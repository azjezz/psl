<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;

/**
 * Open a file handle for write only.
 *
 * @throws Psl\Exception\InvariantViolationException If $filename points to a non-file node, or it not writeable.
 */
function open_file_write_only(string $filename, string $mode = WriteMode::OPEN_OR_CREATE): WriteFileHandleInterface
{
    Psl\invariant(!exists($filename) || is_file($filename), '$filename points to a non-file node.');
    if ($mode === WriteMode::MUST_CREATE && is_file($filename)) {
        Psl\invariant_violation('$filename already exists.');
    } elseif (is_file($filename)) {
        Psl\invariant(is_writeable($filename), '$filename is not writeable.');
    }

    $handle = Internal\open_file($filename, $mode);

    return new Internal\WriteOnlyHandleDecorator($handle);
}
