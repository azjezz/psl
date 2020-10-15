<?php

declare(strict_types=1);

namespace Psl\Internal;

use function get_class;
use function gettype;
use function is_object;

/**
 * @param mixed $value
 *
 * @psalm-pure
 *
 * @codeCoverageIgnore
 *
 * @internal
 */
function type($value): string
{
    return is_object($value) ? get_class($value) : gettype($value);
}
