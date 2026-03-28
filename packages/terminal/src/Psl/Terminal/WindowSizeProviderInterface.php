<?php

declare(strict_types=1);

namespace Psl\Terminal;

/**
 * Provides the current terminal window size.
 *
 * @api
 */
interface WindowSizeProviderInterface
{
    /**
     * Returns the current terminal size as [columns, rows].
     *
     * @return array{int, int}
     */
    public function get(): array;
}
