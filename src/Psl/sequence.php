<?php

declare(strict_types=1);

namespace Psl;

/**
 * This function is a kludge that returns the last argument it receives.
 *
 * @psalm-template T
 *
 * @psalm-param    T ...$args
 *
 * @psalm-return   T|null
 * @param array $args
 * @return mixed|null
 */
function sequence(...$args)
{
    return Iter\last($args);
}
