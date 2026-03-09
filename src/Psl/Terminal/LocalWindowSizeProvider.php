<?php

declare(strict_types=1);

namespace Psl\Terminal;

/**
 * Window size provider for local terminals using stty/tput/PowerShell.
 */
final readonly class LocalWindowSizeProvider implements WindowSizeProviderInterface
{
    /**
     * @return array{int, int} [columns, rows]
     */
    public function get(): array
    {
        return Internal\TerminalSize::get();
    }
}
