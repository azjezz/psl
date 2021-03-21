<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;

/**
 * Open a file handle for read only.
 *
 * @throws Psl\Exception\InvariantViolationException If $filename is not a file, or is not readable.
 */
function open_file_read_only(string $filename): ReadFileHandleInterface
{
  Psl\invariant(is_file($filename), '$filename is not a file.');
  Psl\invariant(is_readable($filename), '$filename is not readable.');

  $handle = Internal\open_file($filename, 'r');

  return new Internal\ReadOnlyHandleDecorator($handle);
}
