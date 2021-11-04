<?php

declare(strict_types=1);

namespace Psl\IO;

use const PHP_SAPI;

/**
 * Return the output handle for the current request.
 *
 * This should generally be used for sending data to clients. In CLI mode, this
 * is usually the process STDOUT.
 *
 * @codeCoverageIgnore
 */
function error_handle(): ?CloseWriteHandleInterface
{
    if (PHP_SAPI === "cli") {
        /** @psalm-suppress MissingThrowsDocblock */
        return new Stream\CloseWriteHandle(
            Internal\open_resource('php://stderr', 'wb')
        );
    }

    return null;
}
