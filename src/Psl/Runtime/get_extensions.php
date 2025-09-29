<?php

declare(strict_types=1);

namespace Psl\Runtime;

/**
 * Returns an list with the names of all extensions compiled and loaded.
 *
 * @return non-empty-list<non-empty-string>
 *
 * @psalm-mutation-free
 */
function get_extensions(): array
{
    return get_loaded_extensions();
}
