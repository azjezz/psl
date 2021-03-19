<?php

declare(strict_types=1);

namespace Psl\IO;

use const PHP_SAPI;

/**
 * Return the input handle for the current request.
 *
 * In CLI mode, this is likely STDIN; for HTTP requests, it may contain the
 * POST data, if any.
 *
 * @codeCoverageIgnore
 */
function input_handle(): ReadHandleInterface
{
    if (PHP_SAPI === "cli") {
        return new Internal\ReadOnlyHandleDecorator(
            Internal\open('php://stdin', 'rb')
        );
    }

    return new Internal\ReadOnlyHandleDecorator(
        Internal\open('php://input', 'rb')
    );
}
