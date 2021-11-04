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
        /** @psalm-suppress MissingThrowsDocblock */
        return new Stream\ReadHandle(
            Internal\open_resource('php://stdin', 'rb')
        );
    }

    /** @psalm-suppress MissingThrowsDocblock */
    return new Stream\ReadHandle(
        Internal\open_resource('php://input', 'rb')
    );
}
