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
function error_handle(): ?WriteHandle
{
    if (PHP_SAPI === "cli") {
        return new Internal\WriteOnlyHandleDecorator(
            Internal\open('php://stderr', 'wb')
        );
    }

    return null;
}
