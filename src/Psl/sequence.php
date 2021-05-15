<?php

declare(strict_types=1);

namespace Psl;

/**
 * This function is a kludge that returns the last argument it receives.
 *
 * @template T
 *
 * @param T ...$args
 *
 * @return T|null
 *
 * @pure
 */
function sequence(...$args)
{
    $result = null;
    foreach ($args as $arg) {
        $result = $arg;
    }

    return $result;
}
