<?php

declare(strict_types=1);

namespace Psl\IO;

use function is_resource;
use function stream_isatty;

/**
 * Check if a stream handle is associated with a terminal device (TTY),
 * which is typically used for interactive input/output.
 *
 * If no handle is provided, it defaults to checking the standard input stream.
 *
 * @return bool True if the stream is a terminal device, false otherwise.
 */
function is_terminal(null|StreamHandleInterface $handle = null): bool
{
    $handle ??= namespace\input_handle();
    $stream = $handle->getStream();

    return is_resource($stream) && @stream_isatty($stream);
}
