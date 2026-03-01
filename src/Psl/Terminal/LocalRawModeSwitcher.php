<?php

declare(strict_types=1);

namespace Psl\Terminal;

/**
 * Raw mode switcher for local terminals using stty/PowerShell.
 */
final readonly class LocalRawModeSwitcher implements RawModeSwitcherInterface
{
    private Internal\RawMode $rawMode;

    public function __construct()
    {
        $this->rawMode = new Internal\RawMode();
    }

    /**
     * Enable raw mode by invoking stty/PowerShell.
     *
     * @throws Exception\RuntimeException If unable to enable raw mode.
     */
    public function enable(): void
    {
        $this->rawMode->enable();
    }

    /**
     * Restore the terminal to its previous state by invoking stty/PowerShell.
     */
    public function restore(): void
    {
        $this->rawMode->restore();
    }
}
