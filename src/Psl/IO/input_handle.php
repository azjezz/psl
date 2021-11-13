<?php

declare(strict_types=1);

namespace Psl\IO;

use Revolt\EventLoop;
use WeakMap;

use const PHP_SAPI;

/**
 * Return the input handle for the current request.
 *
 * In CLI mode, this is likely STDIN; for HTTP requests, it may contain the
 * POST data, if any.
 *
 * @codeCoverageIgnore
 */
function input_handle(): Stream\CloseReadHandleInterface
{
    /** @var WeakMap|null $cache */
    static $cache = null;
    if (null === $cache) {
        $cache = new WeakMap();
    }

    $key = EventLoop::getDriver();
    if ($cache->offsetExists($key)) {
        /** @var Stream\CloseReadHandleInterface */
        return $cache->offsetGet($key);
    }

    if (PHP_SAPI === "cli") {
        $handle = new Stream\CloseReadHandle(
            Internal\open_resource('php://stdin', 'rb')
        );
    } else {
        $handle = new Stream\CloseReadHandle(
            Internal\open_resource('php://input', 'rb')
        );
    }

    $cache->offsetSet($key, $handle);

    return $handle;
}
