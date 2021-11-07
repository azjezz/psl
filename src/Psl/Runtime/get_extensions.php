<?php

declare(strict_types=1);

namespace Psl\Runtime;

/**
 * Returns an list with the names of all extensions compiled and loaded.
 *
 * @return non-empty-list<non-empty-string>
 */
function get_extensions(): array
{
    // we know that this cannot be empty, since some extensions cannot be disabled ( e.g: Core )
    /**
     * @var non-empty-list<non-empty-string>
     */
    return get_loaded_extensions();
}
