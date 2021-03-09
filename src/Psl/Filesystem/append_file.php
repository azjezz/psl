<?php

declare(strict_types=1);

namespace Psl\Filesystem;

use Psl;

/**
 * Append $content to $file.
 *
 * If $file does not exist, it will be created.
 *
 * @throws Psl\Exception\InvariantViolationException If the file specified by
 *                                                   $file is a directory, or is not writeable.
 * @throws Exception\RuntimeException In case of an error.
 */
function append_file(string $file, string $content): void
{
     Internal\write_file($file, $content, true);
}
