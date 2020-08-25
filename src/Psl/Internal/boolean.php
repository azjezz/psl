<?php

declare(strict_types=1);

namespace Psl\Internal;

/**
 * @param mixed $val
 *
 * @psalm-pure
 *
 * @codeCoverageIgnore
 *
 * @internal
 */
function boolean($val): bool
{
    return (bool)$val;
}
