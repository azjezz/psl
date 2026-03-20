<?php

declare(strict_types=1);

namespace Psl\IO;

use function sprintf;

/**
 * Write all of the requested data to the output handle, followed by a new line.
 *
 * The $message will be formatted using the given arguments ( ...$args ).
 *
 * @param int|float|string ...$args
 *
 * @throws Exception\AlreadyClosedException If the output handle has been already closed.
 * @throws Exception\RuntimeException If an error occurred during the operation.
 *
 * @codeCoverageIgnore
 */
function write_line(string $message, mixed ...$args): void
{
    namespace\output_handle()->writeAll(($args === [] ? $message : sprintf($message, ...$args)) . "\n");
}
