<?php

declare(strict_types=1);

namespace Psl\Internal;

/**
 * @param mixed $value
 *
 * @return string
 *
 * @codeCoverageIgnore
 *
 * @internal
 */
function type($value): string
{
    return \is_object($value) ? \get_class($value) : \gettype($value);
}
