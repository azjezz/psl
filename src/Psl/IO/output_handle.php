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
function output_handle(): WriteHandleInterface
{
    if (PHP_SAPI === "cli") {
        return new Internal\WriteOnlyHandleDecorator(
            Internal\open('php://stdout', 'wb')
        );
    }

    return new Internal\WriteOnlyHandleDecorator(
        Internal\open('php://output', 'wb')
    );
}
