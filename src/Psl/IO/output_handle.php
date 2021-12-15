<?php

declare(strict_types=1);

namespace Psl\IO;

use Revolt\EventLoop;
use WeakMap;

use const PHP_SAPI;

/**
 * Return the output handle for the current request.
 *
 * This should generally be used for sending data to clients. In CLI mode, this
 * is usually the process STDOUT.
 *
 * @codeCoverageIgnore
 */
function output_handle(): CloseWriteStreamHandleInterface
{
    /** @var WeakMap|null $cache */
    static $cache = null;
    if (null === $cache) {
        $cache = new WeakMap();
    }

    $key = EventLoop::getDriver();
    if ($cache->offsetExists($key)) {
        /** @var CloseWriteStreamHandleInterface */
        return $cache->offsetGet($key);
    }

    if (PHP_SAPI === "cli") {
        $handle = new CloseWriteStreamHandle(
            Internal\open_resource('php://stdout', 'wb')
        );
    } else {
        $handle = new CloseWriteStreamHandle(
            Internal\open_resource('php://output', 'wb')
        );
    }

    $cache->offsetSet($key, $handle);

    return $handle;
}
