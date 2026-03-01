<?php

declare(strict_types=1);

namespace Psl\Terminal;

/**
 * Manages switching a terminal between raw and cooked mode.
 */
interface RawModeSwitcherInterface
{
    /**
     * Enable raw mode.
     *
     * @throws Exception\RuntimeException If unable to enable raw mode.
     */
    public function enable(): void;

    /**
     * Restore the terminal to its previous state.
     */
    public function restore(): void;
}
