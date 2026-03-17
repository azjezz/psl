<?php

declare(strict_types=1);

namespace Psl\Ansi;

/**
 * A simple control character command (e.g. BEL, BS).
 *
 * @immutable
 */
final readonly class ControlCharacter implements CommandInterface
{
    public function __construct(
        private string $character,
    ) {}

    /**
     * @pure
     */
    public function toString(): string
    {
        return $this->character;
    }

    /**
     * @pure
     */
    public function __toString(): string
    {
        return $this->character;
    }
}
