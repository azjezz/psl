<?php

declare(strict_types=1);

namespace Psl\Env;

/**
 * Returns the arguments which this program was started with (normally passed via the command line).
 *
 * @psalm-return list<string>
 */
function args(): array
{
    /** @psalm-var list<string>|null $args */
    $args = $GLOBALS['argv'] ?? null;
    // @codeCoverageIgnoreStart
    if (null === $args) {
        return [];
    }
    // @codeCoverageIgnoreEnd

    return $args;
}
