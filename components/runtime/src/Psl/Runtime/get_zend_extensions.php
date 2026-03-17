<?php

declare(strict_types=1);

namespace Psl\Runtime;

/**
 * Returns an list with the names of all Zend extensions compiled and loaded.
 *
 * @return list<non-empty-string>
 *
 * @psalm-mutation-free
 */
function get_zend_extensions(): array
{
    /**
     * @var list<non-empty-string>
     */
    return get_loaded_extensions(true);
}
