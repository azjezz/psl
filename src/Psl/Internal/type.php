<?php

declare(strict_types=1);

namespace Psl\Internal;

use Psl\Str;

use function get_debug_type;
use function is_resource;

/**
 * @pure
 *
 * @codeCoverageIgnore
 *
 * @internal
 */
function type(mixed $value): string
{
    if (is_resource($value)) {
        return Str\format('resource<%s>', get_resource_type($value));
    }

    return get_debug_type($value);
}
