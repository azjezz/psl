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
 */
function sequence(...$args)
{
    return Iter\last($args);
}
