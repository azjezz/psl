<?php

declare(strict_types=1);

namespace Psl\IO;

use function sprintf;

/**
 * Write all of the requested data to the error output handle, followed by a new line.
 *
 * The $message will be formatted using the given arguments ( ...$args ).
 *
 * If the error output handle is not available, this function will return immediately.
 *
 * @param int|float|string ...$args
 *
 * @throws Exception\AlreadyClosedException If the output handle has been already closed.
 * @throws Exception\RuntimeException If an error occurred during the operation.
 *
 * @codeCoverageIgnore
 */
function write_error_line(string $message, mixed ...$args): void
{
    namespace\error_handle()?->writeAll(($args === [] ? $message : sprintf($message, ...$args)) . "\n");
}
