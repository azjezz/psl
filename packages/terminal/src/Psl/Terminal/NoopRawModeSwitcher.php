<?php

declare(strict_types=1);

namespace Psl\Terminal;

/**
 * No-op raw mode switcher for remote scenarios where the client manages raw mode.
 */
final class NoopRawModeSwitcher implements RawModeSwitcherInterface
{
    /**
     * No-op since the client is responsible for managing raw mode.
     */
    public function enable(): void {}

    /**
     * No-op since the client is responsible for managing raw mode.
     */
    public function restore(): void {}
}
